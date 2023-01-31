<?php

namespace Sancherie\Feature;

use Sancherie\Feature\Commands\DisableFeature;
use Sancherie\Feature\Commands\EnableFeature;
use Sancherie\Feature\Commands\RevokeFeature;
use Sancherie\Feature\Repositories\FeatureClaimsRepository;
use Sancherie\Feature\Repositories\FeaturesRepository;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FeatureServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $this->app->instance(FeaturesRepository::class, new FeaturesRepository());
        $this->app->instance(FeatureClaimsRepository::class, new FeatureClaimsRepository());
        $this->app->instance(FeatureService::class, $this->app->make(FeatureService::class));

        $package
            ->name('laravel-feature-flags')
            ->hasCommand(EnableFeature::class)
            ->hasCommand(DisableFeature::class)
            ->hasCommand(RevokeFeature::class)
            ->hasMigration('create_features_table')
            ->hasMigration('create_model_has_feature_table')
            ->hasMigration('alter_features_table_add_max_claims');

        if ($this->app->runningInConsole()) {
            $filePath = $this->package->basePath(
                "/../database/migrations/alter_features_table_add_max_claims.php"
            );

            $this->publishes([
                $filePath => $this->generateMigrationName(
                    'alter_features_table_add_max_claims',
                    now()->addSecond()
                ), ], "{$this->package->shortName()}-1.1.0-migrations");
        }
    }
}
