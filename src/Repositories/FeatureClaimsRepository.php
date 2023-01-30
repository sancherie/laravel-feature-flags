<?php

namespace Sancherie\Feature\Repositories;

use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Collection;
use Sancherie\Feature\Contracts\Featurable;
use Sancherie\Feature\Models\Feature;

/**
 * The repository charged of managing feature flag claims.
 */
class FeatureClaimsRepository
{
    /**
     * The cache array of declared features in database.
     *
     * @var Collection<string>|null
     */
    private ?Collection $databaseFeaturesCache = null;

    /**
     * @param Feature|string $feature
     * @param Featurable|null $subject
     * @return bool
     */
    public function isClaimable($feature, ?Featurable $subject = null): bool
    {
        if ($feature instanceof Feature) {
            $feature = $feature->name;
        }

        return $this->getClaimableFeatures($subject)->has($feature);
    }

    /**
     * Return all the features that are claimable for the given subject.
     *
     * @param Featurable|null $subject
     * @return ModelCollection
     */
    public function getClaimableFeatures(?Featurable $subject = null): ModelCollection
    {
        if (is_null($this->databaseFeaturesCache)) {
            $this->databaseFeaturesCache = Feature::query()
                ->whereNotNull('max_claims')
                ->withCount('claimedPivots')
                ->get()
                ->filter(function (Feature $feature) {
                    return is_null($feature->enabled)
                        && $feature->max_claims > $feature->claimed_pivots_count;
                })
                ->keyBy('name');
        }

        if (is_null($subject)) {
            $claimable = $this->databaseFeaturesCache;
        } else {
            $claimable = $this->databaseFeaturesCache->whereNotIn(
                'name',
                app(FeaturesRepository::class)->getSpecificallyEnabledFeatures($subject),
            );
        }

        return $claimable;
    }
}
