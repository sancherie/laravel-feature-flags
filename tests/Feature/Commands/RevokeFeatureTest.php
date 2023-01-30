<?php

use Sancherie\Feature\Repositories\FeaturesRepository;
use function Pest\Laravel\artisan;

use Sancherie\Feature\Database\Factories\UserFactory;
use Sancherie\Feature\Facades\Feature;
use Sancherie\Feature\Tests\Models\User;

it('globally disables a feature', function () {
    Feature::enable('client-v2');

    $command = artisan('feature:revoke', [
        '--force' => true,
        '--feature' => 'client-v2',
    ]);

    $command->assertSuccessful();
    $command->expectsOutput('The feature [client-v2] has been successfully revoked globally !');
    $command->run();
    expect(app(FeaturesRepository::class)->getGlobalFeatureStatus('client-v2'))->toBeNull();
});

it('specifically disables a feature', function () {
    $user = app(UserFactory::class)->create();
    Feature::enable('client-v2');
    Feature::enable('client-v2', $user);

    $command = artisan('feature:revoke', [
        '--force' => true,
        '--feature' => 'client-v2',
        '--model' => User::class,
        '--id' => $user->getKey(),
    ]);

    $command->assertSuccessful();
    $command->expectsOutput(sprintf(
        'The feature [client-v2] has been successfully revoked for %s(%s) !',
        User::class,
        $user->getKey(),
    ));
    $command->run();
    expect(app(FeaturesRepository::class)->getGlobalFeatureStatus('client-v2'))->toBeTrue()
        ->and(app(FeaturesRepository::class)->getSpecificFeatureStatus('client-v2', $user))->toBeNull();
});
