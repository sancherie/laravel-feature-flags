<?php

namespace Sancherie\Feature\Helpers;

use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sancherie\Feature\Models\Feature;
use Sancherie\Feature\Models\FeatureClaim;

/**
 * Helper trait to easily implement Sancherie\Contracts\Featurable on Eloquent models.
 *
 * @mixin Model
 * @property-read ModelCollection<FeatureClaim> $featureClaims
 */
trait WithFeatures
{
    /**
     * Relation to the feature claims.
     *
     * @return MorphMany
     */
    public function featureClaims(): MorphMany
    {
        return $this->morphMany(
            FeatureClaim::class,
            'featurable',
            null,
            null,
            $this->incrementing ? 'uuid' : null,
        );
    }

    /**
     * Assign a specific feature to the featurable model.
     *
     * @param Feature|string $feature
     * @param array $attributes
     * @return FeatureClaim
     */
    public function giveFeature($feature, array $attributes = []): FeatureClaim
    {
        if (is_string($feature)) {
            $feature = Feature::resolveMany([$feature])->first();
        }

        $attributes['feature_id'] = $feature->getKey();
        $attributes['uuid'] = Str::uuid();
        $attributes['enabled'] = true;

        /** @var FeatureClaim $claim */
        $claim = $this->featureClaims()->create($attributes);

        return $claim;
    }

    /**
     * Assign a specific feature to the featurable model using claim.
     *
     * @param Feature|string $feature
     * @return FeatureClaim
     */
    public function claimFeature($feature): FeatureClaim
    {
        return $this->giveFeature($feature, ['claimed_at' => now()]);
    }

    /**
     * Revoke a specific feature from the featurable model.
     *
     * @param Feature|string $feature
     * @return void
     */
    public function revokeFeature($feature): void
    {
        if ($feature instanceof Feature) {
            $feature = $feature->name;
        }

        $claimsIds = $this->featureClaims->whereIn('feature.name', $feature)->modelKeys();
        FeatureClaim::query()->whereKey($claimsIds)->delete();
        $this->unsetRelation('featureClaims');
    }

    /**
     * Disable a specific feature for the featurable model.
     *
     * @param Feature|string $feature
     * @return void
     */
    public function disableFeature($feature): void
    {
        if ($feature instanceof Feature) {
            $feature = $feature->name;
        }

        $claimsIds = $this->featureClaims->whereIn('feature.name', $feature)->modelKeys();
        FeatureClaim::query()->whereKey($claimsIds)->update(['enabled' => false]);
        $this->unsetRelation('featuresClaims');
    }

    /**
     * Return the specific features of the featurable subject.
     *
     * @return Collection<Feature>
     */
    public function getFeatures(): Collection
    {
        return $this->featureClaims->loadMissing('feature')->pluck('feature');
    }

    /**
     * Return the specific features of the featurable subject.
     *
     * @return ModelCollection<FeatureClaim>
     */
    public function getFeatureClaims(): ModelCollection
    {
        return $this->featureClaims;
    }
}
