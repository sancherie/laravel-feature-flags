<?php

namespace Sancherie\Feature;

use Sancherie\Feature\Commands\DisableFeature;
use Sancherie\Feature\Commands\EnableFeature;
use Sancherie\Feature\Commands\RevokeFeature;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FeatureServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $this->app->instance(FeatureService::class, new FeatureService());

        $package
            ->name('laravel-feature-flags')
            ->hasCommand(EnableFeature::class)
            ->hasCommand(DisableFeature::class)
            ->hasCommand(RevokeFeature::class)
            ->hasMigration('create_features_table')
            ->hasMigration('create_model_has_feature_table');
    }
}
