<?php

namespace Sancherie\Feature\Contracts;

use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Collection;
use Sancherie\Feature\Models\Feature;
use Sancherie\Feature\Models\FeatureClaim;

/**
 * Contract to implement for a class to get be specifically featurable.
 */
interface Featurable
{
    /**
     * Assign a specific feature to the featurable model.
     *
     * @param Feature|string $feature
     * @param array $parameters
     * @return FeatureClaim
     */
    public function giveFeature($feature, array $parameters = []): FeatureClaim;

    /**
     * Revoke a specific feature from the featurable model.
     *
     * @param Feature|string $feature
     * @return void
     */
    public function revokeFeature($feature): void;

    /**
     * Disable a specific feature for the featurable model.
     *
     * @param Feature|string $feature
     * @return void
     */
    public function disableFeature($feature): void;

    /**
     * Claim a specific feature.
     *
     * @param Feature|string $feature
     * @return FeatureClaim
     */
    public function claimFeature($feature): FeatureClaim;

    /**
     * Return the specific features of the featurable subject.
     *
     * @return Collection<string>
     */
    public function getFeatures(): Collection;

    /**
     * Return the specific features of the featurable subject.
     *
     * @return ModelCollection<FeatureClaim>
     */
    public function getFeatureClaims(): ModelCollection;

    /**
     * Return a unique identifier of the featurable subject.
     *
     * @return mixed
     */
    public function getKey();
}
