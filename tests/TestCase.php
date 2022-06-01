<?php

namespace Sancherie\Feature\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Sancherie\Feature\FeatureServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Sancherie\\Feature\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            FeatureServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_users_table.php';
        $migration->up();
        $migration = include __DIR__.'/../database/migrations/create_features_table.php';
        $migration->up();
        $migration = include __DIR__.'/../database/migrations/create_model_has_feature_table.php';
        $migration->up();
    }
}
