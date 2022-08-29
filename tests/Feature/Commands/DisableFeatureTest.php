<?php

use function Pest\Laravel\artisan;

use Sancherie\Feature\Database\Factories\UserFactory;
use Sancherie\Feature\Facades\Feature;
use Sancherie\Feature\Tests\Models\User;

it('globally disables a feature', function () {
    Feature::enable('client-v2');

    $command = artisan('feature:disable', [
        '--force' => true,
        '--feature' => 'client-v2',
    ]);

    $command->assertSuccessful();
    $command->expectsOutput('The feature [client-v2] has been successfully disabled globally !');
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeFalse();
});

it('specifically disables a feature', function () {
    $user = app(UserFactory::class)->create();
    Feature::enable('client-v2');
    Feature::enable('client-v2', $user);

    $command = artisan('feature:disable', [
        '--force' => true,
        '--feature' => 'client-v2',
        '--model' => User::class,
        '--id' => $user->getKey(),
    ]);

    $command->assertSuccessful();
    $command->expectsOutput(sprintf(
        'The feature [client-v2] has been successfully disabled for %s(%s) !',
        User::class,
        $user->getKey(),
    ));
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeTrue()
        ->and(Feature::getSpecificFeatureStatus('client-v2', $user))->toBeFalse();
});
