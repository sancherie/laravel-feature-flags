<?php

use Sancherie\Feature\FeatureService;
use Sancherie\Feature\Tests\Models\User;

it('declare a feature without second arg', function () {
    $service = app(FeatureService::class);

    $service->declare('client-v2');

    expect($service->isEnabled('client-v2'))->toBeTrue()
        ->and($service->enabled('client-v2'))->toBeTrue();
});

it('returns the enabled features for a subject', function () {
    /** @var User $user */
    $user = User::factory()->create();
    /** @var User $other */
    $other = User::factory()->create();
    $service = app(FeatureService::class);

    $service->declare('client-v2');
    $service->enable('client-v3');
    $service->enable('client-v4', $user);
    $service->enable('client-v5', $other);

    expect($service->enabled($user)->sort()->values()->all())->toBe(['client-v2', 'client-v3', 'client-v4']);
});

it('returns only one feature instance when its given multiple times', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $service = app(FeatureService::class);
    $service->enable('client-v2');
    $service->enable('client-v2', $user);

    expect($service->getEnabledFeatures($user)->all())->toBe(['client-v2']);
});
