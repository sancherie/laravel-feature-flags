<?php

namespace Sancherie\Feature\Helpers;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sancherie\Feature\Models\Feature;
use Sancherie\Feature\Models\ModelHasFeature;

/**
 * Helper trait to easily implement Sancherie\Contracts\Featurable on Eloquent models.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property-read \Illuminate\Database\Eloquent\Collection<Feature> $directFeatures
 * @property-read \Illuminate\Database\Eloquent\Collection<ModelHasFeature> $featureClaims
 */
trait WithFeatures
{
    /**
     * Relation to the direct features.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function directFeatures(): MorphToMany
    {
        return $this->morphToMany(
            Feature::class,
            'featurable',
            'model_has_feature',
            'featurable_id',
            'feature_id',
            $this->incrementing ? 'uuid' : null,
            'id'
        )->withPivot(['uuid as pivot_uuid', 'enabled as direct_enabled']);
    }

    /**
     * Relation to the feature claims.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function featureClaims(): MorphMany
    {
        return $this->morphMany(
            ModelHasFeature::class,
            'featurable',
            'featurable_type',
            'featurable_id',
            $this->incrementing ? 'uuid' : null,
            'id'
        );
    }

    /**
     * Assign a specific feature to the featurable model.
     *
     * @param ...$features
     * @return void
     */
    public function giveFeature(...$features): void
    {
        $directFeatures = $this->directFeatures
            ->mapWithKeys(fn (Feature $feature) => [$feature->getKey() => ['enabled' => $feature->direct_enabled]])
            ->replace(
                Feature::resolveMany($features)
                    ->mapWithKeys(fn (Feature $feature) => [$feature->getKey() => [
                        'uuid' => Str::uuid(),
                        'enabled' => true,
                    ]])
            )->all();

        $this->directFeatures()->sync($directFeatures);
        $this->unsetRelation('directFeatures');
    }

    /**
     * Assign a specific feature to the featurable model using claim.
     *
     * @param ...$features
     * @return void
     */
    public function claimFeature(...$features): void
    {
        $directFeatures = $this->directFeatures
            ->mapWithKeys(fn (Feature $feature) => [$feature->getKey() => ['enabled' => $feature->direct_enabled]])
            ->replace(
                Feature::resolveMany($features)
                    ->mapWithKeys(fn (Feature $feature) => [$feature->getKey() => [
                        'uuid' => Str::uuid(),
                        'enabled' => true,
                        'claimed_at' => now(),
                    ]])
            )->all();

        $this->directFeatures()->sync($directFeatures);
        $this->unsetRelation('directFeatures');
    }

    /**
     * Revoke a specific feature from the featurable model.
     *
     * @param ...$features
     * @return void
     */
    public function revokeFeature(...$features): void
    {
        $this->directFeatures()->detach(
            Feature::resolveMany($features)->modelKeys()
        );
    }

    /**
     * Disable a specific feature for the featurable model.
     *
     * @param ...$features
     * @return void
     */
    public function disableFeature(...$features): void
    {
        $directFeatures = $this->directFeatures
            ->mapWithKeys(fn (Feature $feature) => [$feature->getKey() => ['enabled' => $feature->direct_enabled]])
            ->replace(
                Feature::resolveMany($features)
                    ->mapWithKeys(fn (Feature $feature) => [$feature->getKey() => ['enabled' => false]])
            )->all();

        $this->directFeatures()->sync($directFeatures);
        $this->unsetRelation('directFeatures');
    }

    /**
     * Return the specific features of the featurable subject.
     *
     * @return Collection
     */
    public function getFeatures(): Collection
    {
        return $this->directFeatures->pluck('direct_enabled', 'name');
    }
}
