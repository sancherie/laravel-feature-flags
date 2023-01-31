<?php

use Sancherie\Feature\Database\Factories\UserFactory;
use Sancherie\Feature\FeatureService;
use Sancherie\Feature\Models\Feature;
use Sancherie\Feature\Repositories\FeatureClaimsRepository;

it('should return not claimable features', function () {
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'max_claims' => null]);
    Feature::query()->create(['name' => 'client-v3', 'max_claims' => 0]);
    Feature::query()->create(['name' => 'client-v4', 'max_claims' => 0]);


    expect($service->getClaimableFeatures())
        ->toBeCollection()
        ->toBeEmpty();
});

it('should return three claimable features', function () {
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'claimable' => true, 'max_claims' => 1]);
    Feature::query()->create(['name' => 'client-v3', 'claimable' => true, 'max_claims' => 1500]);
    Feature::query()->create(['name' => 'client-v4', 'claimable' => true]);

    expect($service->claimable())
        ->toBeCollection()
        ->toHaveCount(3);
});

it('should return only one claimable feature', function () {
    $users = app(UserFactory::class)->count(3)->create();
    /** @var FeatureService $service */
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'claimable' => true, 'max_claims' => 2]);
    Feature::query()->create(['name' => 'client-v3', 'claimable' => true, 'max_claims' => 1500]);

    $users[0]->claimFeature('client-v2');
    $users[1]->giveFeature('client-v2');

    expect($service->getClaimableFeatures())
        ->toBeCollection()
        ->toHaveCount(2);

    $users[2]->claimFeature('client-v2');

    app(FeatureClaimsRepository::class)->forgetCache();
    expect($service->getClaimableFeatures())
        ->toBeCollection()
        ->toHaveCount(1);
});

it('should return not claimable feature because disabled', function () {
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'max_claims' => 2, 'enabled' => false]);

    expect($service->getClaimableFeatures())
        ->toBeCollection()
        ->toBeEmpty();
});

it('should return not claimable feature because the user already claimed it', function () {
    $user = app(UserFactory::class)->create();
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'max_claims' => 2]);

    $user->giveFeature('client-v2');

    expect($service->getClaimableFeatures($user))
        ->toBeCollection()
        ->toBeEmpty();
});

it('should not return claimable feature because the feature is globally enabled', function () {
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'max_claims' => 2, 'enabled' => true]);

    expect($service->getClaimableFeatures())
        ->toBeCollection()
        ->toBeEmpty();
});

it('should return true because the feature the max claims is big', function () {
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'claimable' => true, 'max_claims' => 2]);

    expect($service->isClaimable('client-v2'))->toBeTrue();
});

it('should return false because the max claims is null', function () {
    $user = app(UserFactory::class)->create();
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2']);

    expect($service->isClaimable('client-v2'))->toBeFalse();
});

it('should return false because the max claims is reached', function () {
    $user = app(UserFactory::class)->create();
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'max_claims' => 1]);

    $user->claimFeature('client-v2');

    expect($service->isClaimable('client-v2'))->toBeFalse();
});

it('should return true because feature is given but not claimed', function () {
    $user = app(UserFactory::class)->create();
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'claimable' => true, 'max_claims' => 1]);

    $user->giveFeature('client-v2');

    expect($service->claimable('client-v2'))->toBeTrue();
});

it('should return false because feature is globally enabled', function () {
    $service = app(FeatureService::class);
    $feature = Feature::query()->create([
        'name' => 'client-v2',
        'max_claims' => 1,
        'enabled' => true,
    ]);

    expect($service->isClaimable($feature))->toBeFalse();
});

it('should return false because feature is globally disabled', function () {
    $service = app(FeatureService::class);
    Feature::query()->create(['name' => 'client-v2', 'max_claims' => 1, 'enabled' => false]);

    expect($service->isClaimable('client-v2'))->toBeFalse();
});
