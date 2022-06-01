<?php

use Sancherie\Feature\FeatureService;
use Sancherie\Feature\Tests\Models\User;

it('declares a programmatic feature with a bool value', function () {
    $service = new FeatureService();

    $service->declare('client-v2', true);
    $service->declare('client-v3', false);

    expect($service->isProgrammaticallyEnabled('client-v2'))->toBe(true)
        ->and($service->isProgrammaticallyEnabled('client-v3'))->toBe(false)
        ->and($service->isProgrammaticallyEnabled('client-v4'))->toBe(false);
});

it('declares a programmatic feature with a callback', function () {
    $service = new FeatureService();

    $service->declare('client-v2', fn () => true);
    $service->declare('client-v3', fn () => false);

    expect($service->isProgrammaticallyEnabled('client-v2'))->toBe(true)
        ->and($service->isProgrammaticallyEnabled('client-v3'))->toBe(false);
});

it('declare an array of features', function () {
    $service = new FeatureService();

    $service->declare([
        'client-v2' => true,
        'client-v3' => false,
        'client-v4' => fn () => true,
    ]);

    expect($service->isProgrammaticallyEnabled('client-v2'))->toBe(true)
        ->and($service->isProgrammaticallyEnabled('client-v3'))->toBe(false)
        ->and($service->isProgrammaticallyEnabled('client-v4'))->toBe(true);
});

it('declare an empty array of features', function () {
    $service = new FeatureService();

    $service->declare([]);

    expect($service->getProgrammaticallyEnabledFeatures())->toBeEmpty();
});

it('declare a feature without second arg', function () {
    $service = new FeatureService();

    $service->declare('client-v2');

    expect($service->isProgrammaticallyEnabled('client-v2'))->toBeTrue()
        ->and($service->isEnabled('client-v2'))->toBeTrue()
        ->and($service->enabled('client-v2'))->toBeTrue();
});

it('returns the enabled features for a subject', function () {
    /** @var \Sancherie\Feature\Tests\Models\User $user */
    $user = User::factory()->create();
    /** @var \Sancherie\Feature\Tests\Models\User $other */
    $other = User::factory()->create();
    $service = new FeatureService();

    $service->declare('client-v2');
    $service->enable('client-v3');
    $service->enable('client-v4', $user);
    $service->enable('client-v5', $other);

    expect($service->enabled($user)->sort()->values()->all())->toBe(['client-v2', 'client-v3', 'client-v4']);
});

it('returns no specifically enabled features if no subject given', function () {
    $service = new FeatureService();

    expect($service->getSpecificallyEnabledFeatures())->toBeEmpty();
});

it('enables the given feature because no subject is taken', function () {
    /** @var \Sancherie\Feature\Tests\Models\User $user */
    $user = User::factory()->create();
    $service = new FeatureService();

    $service->declare('client-v2', fn () => true);

    expect($service->isProgrammaticallyEnabled('client-v2', $user))->toBeTrue();
});

it('enables the given feature because the subject is mandatory and present', function () {
    /** @var \Sancherie\Feature\Tests\Models\User $user */
    $user = User::factory()->create();
    $service = new FeatureService();

    $service->declare('client-v2', fn (User $user) => true);

    expect($service->isProgrammaticallyEnabled('client-v2', $user))->toBeTrue();
});

it('doesnt enable the given feature because the subject is mandatory and missing', function () {
    /** @var \Sancherie\Feature\Tests\Models\User $user */
    $user = User::factory()->create();
    $service = new FeatureService();

    $service->declare('client-v2', fn (User $user) => true);

    expect($service->isProgrammaticallyEnabled('client-v2'))->toBeFalse();
});

it('enables the given feature because the subject is optional and missing', function () {
    $service = new FeatureService();

    $service->declare('client-v2', fn (?User $user) => true);

    expect($service->isProgrammaticallyEnabled('client-v2'))->toBeTrue();
});

it('throws an error because there are too many wanted arguments', function () {
    $service = new FeatureService();

    $service->declare('client-v2', fn ($user, $user2) => true);

    expect(function () use ($service) {
        $service->isProgrammaticallyEnabled('client-v2');
    })->toThrow(Exception::class, 'Feature flag callback can receive a maximum of 1 argument.');
});

it('throws an error because the wanted argument is not typed', function () {
    $service = new FeatureService();

    $service->declare('client-v2', fn ($user) => true);

    expect(function () use ($service) {
        $service->isProgrammaticallyEnabled('client-v2');
    })->toThrow(Exception::class, 'Feature flag callback argument should be typed.');
});

it('throws an error because the wanted argument is a builtin', function () {
    $service = new FeatureService();

    $service->declare('client-v2', fn (int $user) => true);

    expect(function () use ($service) {
        $service->isProgrammaticallyEnabled('client-v2');
    })->toThrow(
        Exception::class,
        'Feature flag callback argument should be an instance of Featurable.'
    );
});
