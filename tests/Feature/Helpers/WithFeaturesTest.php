<?php

use Sancherie\Feature\Tests\Models\User;

it('specifically enables a feature with giveFeature()', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $user->giveFeature('client-v2', 'client-v3');

    expect($user->directFeatures->pluck('name')->all())
        ->toBeArray()
        ->toBe(['client-v2', 'client-v3']);
});


it('specifically disables a feature with revokeFeature()', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $user->giveFeature('client-v2', 'client-v3');
    $user->revokeFeature('client-v2');

    expect($user->directFeatures->pluck('name')->all())
        ->toBeArray()
        ->toBe(['client-v3']);
});
