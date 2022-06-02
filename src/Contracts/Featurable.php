<?php

namespace Sancherie\Feature\Contracts;

use Illuminate\Support\Collection;

/**
 * Contract to implement for a class to get be specifically featurable.
 */
interface Featurable
{
    /**
     * Assign a specific feature to the featurable model.
     *
     * @param array $features
     * @return void
     */
    public function giveFeature(...$features): void;

    /**
     * Revoke a specific feature from the featurable model.
     *
     * @param array $features
     * @return void
     */
    public function revokeFeature(...$features): void;

    /**
     * Return the specific features of the featurable subject.
     *
     * @return Collection<string>
     */
    public function getFeatures(): Collection;

    /**
     * Return a unique identifier of the featurable subject.
     *
     * @return mixed
     */
    public function getKey();
}
