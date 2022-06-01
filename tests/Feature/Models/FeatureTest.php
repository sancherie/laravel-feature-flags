<?php

use Illuminate\Support\Str;
use Sancherie\Feature\Models\Feature;

it('resolves all existing features when using resolveMany()', function () {
    /** @var Feature $clientV2 */
    $clientV2 = Feature::query()->create(['name' => 'client-v2', 'enabled' => true]);
    /** @var Feature $clientV3 */
    $clientV3 = Feature::query()->create(['name' => 'client-v3', 'enabled' => false]);

    $features = Feature::resolveMany(['client-v2', 'client-v3'])->sortBy('name');

    expect($features[0]->getKey())->toBe($clientV2->getKey())
        ->and($features[0]->enabled)->toBe($clientV2->enabled)
        ->and($features[1]->getKey())->toBe($clientV3->getKey())
        ->and($features[1]->enabled)->toBe($clientV3->enabled);
});

it('creates new features is not existing when using resolveMany()', function () {
    /** @var Feature $clientV2 */
    $clientV2 = Feature::query()->create(['name' => 'client-v2', 'enabled' => true]);

    $features = Feature::resolveMany(['client-v2', 'client-v3'])->sortBy('name');

    expect($features[0]->getKey())->toBe($clientV2->getKey())
        ->and($features[0]->enabled)->toBeTrue
        ->and($features[1]->exists)->toBeTrue
        ->and($features[1]->enabled)->false;
});

it('doesnt reassign uuid on creation if already set', function () {
    $id = (string) Str::uuid();
    /** @var Feature $clientV2 */
    $clientV2 = Feature::query()->make(['name' => 'client-v2', 'enabled' => true]);
    $clientV2->forceFill(['id' => $id]);
    $clientV2->save();

    expect($clientV2->refresh()->getKey())->toBe($id);
});
