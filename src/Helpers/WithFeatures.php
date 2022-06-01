<?php

namespace Sancherie\Feature\Helpers;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Sancherie\Feature\Models\Feature;

/**
 * Helper trait to easily implement Sancherie\Contracts\Featurable on Eloquent models.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property-read \Illuminate\Database\Eloquent\Collection<Feature> $directFeatures
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
        $this->directFeatures()->syncWithoutDetaching(
            Feature::resolveMany($features)
        );
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
     * Return the specific features of the featurable subject.
     *
     * @return Collection
     */
    public function getFeatures(): Collection
    {
        return $this->directFeatures->pluck('name');
    }
}
