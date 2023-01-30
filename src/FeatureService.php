<?php

namespace Sancherie\Feature;

use Exception;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use ReflectionException;
use Sancherie\Feature\Contracts\Featurable;
use Sancherie\Feature\Models\Feature;
use Sancherie\Feature\Repositories\FeatureClaimsRepository;
use Sancherie\Feature\Repositories\FeaturesRepository;

/**
 * The service charged of managing feature flags.
 */
class FeatureService
{
    use Macroable;

    /**
     * The repository managing features.
     *
     * @var FeaturesRepository
     */
    private FeaturesRepository $featuresRepository;

    /**
     * The repository managing feature claims.
     *
     * @var FeatureClaimsRepository
     */
    private FeatureClaimsRepository $featureClaimsRepository;

    /**
     * @param FeaturesRepository $featuresRepository
     * @param FeatureClaimsRepository $featureClaimsRepository
     */
    public function __construct(
        FeaturesRepository $featuresRepository,
        FeatureClaimsRepository $featureClaimsRepository
    ) {
        $this->featuresRepository = $featuresRepository;
        $this->featureClaimsRepository = $featureClaimsRepository;
    }

    /**
     * Register new programmatic feature(s) and its(their) rule to be enabled.
     *
     * @param string|array $feature
     * @param bool|callable|null $enabled
     * @return void
     * @throws Exception
     */
    public function declare($feature, $enabled = null): void
    {
        $this->featuresRepository->declare(...func_get_args());
    }

    /**
     * Enable the given feature. If a featurable is given, the feature is enabled only for this subject.
     *
     * @param string $feature
     * @param Featurable|null $for
     * @return void
     */
    public function enable(string $feature, ?Featurable $for = null): void
    {
        $this->featuresRepository->enable($feature, $for);
    }

    /**
     * Revoke the given feature. If a featurable is given, the feature is revoked for the subject.
     *
     * @param string $feature
     * @param Featurable|null $for
     * @return void
     */
    public function revoke(string $feature, ?Featurable $for = null): void
    {
        $this->featuresRepository->revoke($feature, $for);
    }

    /**
     * Disable the given feature. If a featurable is given, the feature is disabled for the subject.
     *
     * @param string $feature
     * @param Featurable|null $for
     * @return void
     */
    public function disable(string $feature, ?Featurable $for = null): void
    {
        $this->featuresRepository->disable($feature, $for);
    }

    /**
     * If a feature name (string) is given, check whether the feature is enabled or not,
     * otherwise it returns the list of enabled features. A featurable object can be given.
     *
     * @param string|Featurable|null $feature
     * @return bool|Collection<string>
     * @throws ReflectionException
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
     * Check if the current feature is enabled.
     *
     * @param string $feature
     * @param Featurable|null $featurable
     * @return bool
     * @throws ReflectionException
     */
    public function isEnabled(string $feature, ?Featurable $featurable = null): bool
    {
        return $this->featuresRepository->isEnabled($feature, $featurable);
    }

    /**
     * Return all the enabled features for the given featurable.
     *
     * @param Featurable|null $featurable
     * @return Collection<string>
     */
    public function getEnabledFeatures(?Featurable $featurable = null): Collection
    {
        return $this->featuresRepository->getEnabledFeatures($featurable);
    }

    /**
     * If a feature name (string) is given, check whether the feature is claimable or not,
     * otherwise it returns the list of claimable features. A featurable object can be given.
     *
     * @param string|Featurable|Feature|null $feature
     * @param Featurable|null $featurable
     * @return bool|ModelCollection
     */
    public function claimable($feature = null, ?Featurable $featurable = null)
    {
        if (is_string($feature) || $feature instanceof Feature) {
            $result = $this->isClaimable($feature, $featurable);
        } else {
            $result = $this->getClaimableFeatures($feature);
        }

        return $result;
    }

    /**
     * Return whether the given feature is claimable or not.
     *
     * @param Feature|string $feature
     * @param Featurable|null $subject
     * @return bool
     */
    public function isClaimable($feature, ?Featurable $subject = null): bool
    {
        return $this->featureClaimsRepository->isClaimable($feature, $subject);
    }

    /**
     * Return all the features that are claimable for the given subject.
     *
     * @param Featurable|null $subject
     * @return ModelCollection
     */
    public function getClaimableFeatures(?Featurable $subject = null): ModelCollection
    {
        return $this->featureClaimsRepository->getClaimableFeatures($subject);
    }
}
