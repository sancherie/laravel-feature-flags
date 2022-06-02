<?php

namespace Sancherie\Feature;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use ReflectionFunction;
use Sancherie\Feature\Contracts\Featurable;
use Sancherie\Feature\Models\Feature;

/**
 * The service charged of managing feature flags.
 */
class FeatureService
{
    use Macroable;

    /**
     * The array of declared features.
     *
     * @var array<string, \Closure|bool>
     */
    private array $features = [];

    /**
     * The cache array of declared features in database.
     *
     * @var Collection<string>|null
     */
    private ?Collection $databaseFeaturesCache = null;

    /**
     * Register new programmatic feature(s) and its(their) rule to be enabled.
     *
     * @param string|array $feature
     * @param bool|callable|null $enabled
     * @return void
     * @throws \Exception
     */
    public function declare($feature, $enabled = null): void
    {
        if (empty($feature)) {
            return;
        }

        if (is_array($feature)) {
            foreach ($feature as $key => $value) {
                if (is_string($key)) {
                    $this->declare($key, $value);
                } else {
                    $this->declare($value);
                }
            }

            return;
        }

        if (func_num_args() === 1) {
            $enabled = true;
        }

        if (is_bool($enabled) || is_callable($enabled)) {
            $this->features[$feature] = $enabled;
        } else {
            throw new Exception('Invalid rule type, it should be either a boolean or a closure');
        }
    }

    /**
     * Enable the given feature. If a featurable is given, the feature is enabled only for this subject.
     *
     * @param string $feature
     * @param \Sancherie\Feature\Contracts\Featurable|null $for
     * @return void
     */
    public function enable(string $feature, ?Featurable $for = null): void
    {
        if (is_null($for)) {
            Feature::query()->updateOrCreate(['name' => $feature], ['enabled' => true]);
        } else {
            $for->giveFeature($feature);
        }
    }

    /**
     * Disable the given feature. If a featurable is given, the feature is revoked for the subject.
     *
     * @param string $feature
     * @param \Sancherie\Feature\Contracts\Featurable|null $for
     * @return void
     */
    public function disable(string $feature, ?Featurable $for = null): void
    {
        if (is_null($for)) {
            Feature::query()->updateOrCreate(['name' => $feature], ['enabled' => false]);
        } else {
            $for->revokeFeature($feature);
        }
    }

    /**
     * If a feature name (string) is given, check whether the feature is enabled or not,
     * otherwise it returns the list of enabled features. A featurable object can be given.
     *
     * @param string|Featurable|null $feature
     * @return bool|\Illuminate\Database\Eloquent\Collection<string>
     * @throws \ReflectionException
     */
    public function enabled($feature = null, ?Featurable $featurable = null)
    {
        if (is_string($feature)) {
            $result = $this->isEnabled($feature, $featurable);
        } else {
            $result = $this->getEnabledFeatures($feature);
        }

        return $result;
    }

    /**
     * Return all the enabled features for the given featurable.
     *
     * @param Featurable|null $featurable
     * @return Collection<string>
     */
    public function getEnabledFeatures(?Featurable $featurable = null): Collection
    {
        return $this->getGloballyEnabledFeatures()
            ->merge($this->getSpecificallyEnabledFeatures($featurable))
            ->merge($this->getProgrammaticallyEnabledFeatures($featurable))
            ->unique();
    }

    /**
     * Return all the features that are globally enabled in database.
     *
     * @return Collection<string>
     */
    public function getGloballyEnabledFeatures(): Collection
    {
        if (is_null($this->databaseFeaturesCache)) {
            $this->databaseFeaturesCache = Feature::query()
                ->where('enabled', true)
                ->pluck('name')
                ->unique();
        }

        return $this->databaseFeaturesCache;
    }

    /**
     * Return all specifically enabled feature for the given featurable.
     *
     * @param Featurable|null $featurable
     * @return Collection<string>
     */
    public function getSpecificallyEnabledFeatures(?Featurable $featurable = null): Collection
    {
        if (is_null($featurable)) {
            return Collection::empty();
        }

        return $featurable->getFeatures();
    }

    /**
     * Return all the features that are programmatically enabled for the given featurable.
     *
     * @param \Sancherie\Feature\Contracts\Featurable|null $featurable
     * @return \Illuminate\Support\Collection
     */
    public function getProgrammaticallyEnabledFeatures(?Featurable $featurable = null): Collection
    {
        return Collection::make($this->features)
            ->keys()
            ->filter(fn (string $feature) => $this->isProgrammaticallyEnabled($feature, $featurable));
    }

    /**
     * Check if the current feature is enabled.
     *
     * @param string $feature
     * @param \Sancherie\Feature\Contracts\Featurable|null $featurable
     * @return bool
     * @throws \ReflectionException
     */
    public function isEnabled(string $feature, ?Featurable $featurable = null): bool
    {
        return $this->isGloballyEnabled($feature)
            || $this->isSpecificallyEnabled($feature, $featurable)
            || $this->isProgrammaticallyEnabled($feature, $featurable);
    }

    /**
     * Check if the given feature is globally enabled in database.
     *
     * @param string $feature
     * @return bool
     */
    public function isGloballyEnabled(string $feature): bool
    {
        return $this->getGloballyEnabledFeatures()->contains($feature);
    }

    /**
     * Check if the given feature is specifically enabled for the featurable.
     *
     * @param string $feature
     * @param \Sancherie\Feature\Contracts\Featurable|null $featurable
     * @return bool
     */
    public function isSpecificallyEnabled(string $feature, ?Featurable $featurable): bool
    {
        if (is_null($featurable)) {
            return false;
        }

        return $featurable->getFeatures()->contains($feature);
    }

    /**
     * Check if the given feature is programmatically enabled.
     *
     * @param string $feature
     * @param \Sancherie\Feature\Contracts\Featurable|null $featurable
     * @return bool
     * @throws \ReflectionException
     */
    public function isProgrammaticallyEnabled(string $feature, ?Featurable $featurable = null): bool
    {
        $rule = $this->features[$feature] ?? false;

        if (is_bool($rule)) {
            return $rule;
        }

        $argument = $this->getCallbackArgumentData($rule);

        if (! $argument['optional'] && ! ($featurable instanceof $argument['class'])) {
            return false;
        }

        return $rule($featurable);
    }

    /**
     * Return data about the first callback argument
     *
     * @param \Closure $callback
     * @return array
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function getCallbackArgumentData(\Closure $callback): array
    {
        $parameters = (new ReflectionFunction($callback))->getParameters();

        if (empty($parameters)) {
            return [
                'optional' => true,
            ];
        } elseif (count($parameters) > 1) {
            throw new Exception('Feature flag callback can receive a maximum of 1 argument.');
        }

        $parameter = $parameters[0];

        if (is_null($type = $parameter->getType())) {
            throw new Exception('Feature flag callback argument should be typed.');
        }

        if ($type->isBuiltin()) {
            throw new Exception('Feature flag callback argument should be an instance of Featurable.');
        }

        return [
            'name' => $parameter->getName(),
            'class' => $type instanceof \ReflectionNamedType ? $type->getName() : null,
            'optional' => $type->allowsNull(),
        ];
    }

    public function forgetCache(): void
    {
        $this->databaseFeaturesCache = null;
    }
}
