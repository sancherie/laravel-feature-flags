<?php

use function Pest\Laravel\artisan;

use Sancherie\Feature\Contracts\Featurable;
use Sancherie\Feature\Database\Factories\UserFactory;
use Sancherie\Feature\Facades\Feature;
use Sancherie\Feature\Tests\Models\NotFeaturableUser;
use Sancherie\Feature\Tests\Models\User;
use Symfony\Component\Console\Command\Command;

it('globally enables a feature with force', function () {
    $command = artisan('feature:enable', [
        '--force' => true,
        '--feature' => 'client-v2',
    ]);

    $command->assertSuccessful();
    $command->expectsOutput('The feature [client-v2] has been successfully enabled globally !');
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeTrue();
});

it('globally enables a feature with confirmation', function () {
    $command = artisan('feature:enable', [
        '--feature' => 'client-v2',
    ]);

    $command->assertSuccessful();
    $command->expectsConfirmation(
        'You are about to globally enable the feature [client-v2]. Do you want to continue ?',
        'yes'
    );
    $command->expectsOutput('The feature [client-v2] has been successfully enabled globally !');
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeTrue();
});

it('doesnt globally enable a feature without confirmation', function () {
    $command = artisan('feature:enable', [
        '--feature' => 'client-v2',
    ]);

    $command->assertSuccessful();
    $command->expectsConfirmation(
        'You are about to globally enable the feature [client-v2]. Do you want to continue ?',
    );
    $command->expectsOutput('Action canceled');
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull();
});

it('specifically enables a feature with force', function () {
    $user = app(UserFactory::class)->create();

    $command = artisan('feature:enable', [
        '--force' => true,
        '--feature' => 'client-v2',
        '--model' => User::class,
        '--id' => $user->getKey(),
    ]);

    $command->assertSuccessful();
    $command->expectsOutput(sprintf(
        'The feature [client-v2] has been successfully enabled for %s(%s) !',
        User::class,
        $user->getKey(),
    ));
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull()
        ->and(Feature::getSpecificFeatureStatus('client-v2', $user))->toBeTrue();
});

it('specifically enables a feature with confirmation', function () {
    /** @var \Sancherie\Feature\Tests\Models\User $user */
    $user = app(UserFactory::class)->create();

    $command = artisan('feature:enable', [
        '--feature' => 'client-v2',
        '--model' => User::class,
        '--id' => $user->getKey(),
    ]);

    $command->assertSuccessful();
    $command->expectsConfirmation(sprintf(
        'You are about to enable the feature [client-v2] for the featurable %s(%s). Do you want to continue ?',
        User::class,
        $user->getKey(),
    ), 'yes');
    $command->expectsOutput(sprintf(
        'The feature [client-v2] has been successfully enabled for %s(%s) !',
        User::class,
        $user->getKey(),
    ));
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull()
        ->and(Feature::getSpecificFeatureStatus('client-v2', $user))->toBeTrue();
});

it('doesnt specifically enable a feature without confirmation', function () {
    /** @var \Sancherie\Feature\Tests\Models\User $user */
    $user = app(UserFactory::class)->create();

    $command = artisan('feature:enable', [
        '--feature' => 'client-v2',
        '--model' => User::class,
        '--id' => $user->getKey(),
    ]);

    $command->assertSuccessful();
    $command->expectsConfirmation(sprintf(
        'You are about to enable the feature [client-v2] for the featurable %s(%s). Do you want to continue ?',
        User::class,
        $user->getKey(),
    ), );
    $command->expectsOutput('Action canceled');
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull()
        ->and(Feature::getSpecificFeatureStatus('client-v2', $user))->toBeNull();
});

it('specifically enables a feature by email', function () {
    /** @var \Sancherie\Feature\Tests\Models\User $user */
    $user = app(UserFactory::class)->create();

    $command = artisan('feature:enable', [
        '--force' => true,
        '--feature' => 'client-v2',
        '--model' => User::class,
        '--email' => $user->email,
    ]);

    $command->assertSuccessful();
    $command->expectsOutput(sprintf(
        'The feature [client-v2] has been successfully enabled for %s(%s) !',
        User::class,
        $user->email,
    ));
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull()
        ->and(Feature::getSpecificFeatureStatus('client-v2', $user))->toBeTrue();
});

it('asks the feature name if not given as an option', function () {
    /** @var \Sancherie\Feature\Tests\Models\User $user */
    $user = app(UserFactory::class)->create();

    $command = artisan('feature:enable', [
        '--force' => true,
        '--model' => User::class,
        '--email' => $user->email,
    ]);

    $command->assertSuccessful();
    $command->expectsQuestion('Which feature do you want to enable ?', 'client-v2');
    $command->expectsOutput(sprintf(
        'The feature [client-v2] has been successfully enabled for %s(%s) !',
        User::class,
        $user->email,
    ));
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull()
        ->and(Feature::getSpecificFeatureStatus('client-v2', $user))->toBeTrue();
});

it('throws an error when no feature name if given', function () {
    $command = artisan('feature:enable');

    $command->assertExitCode(Command::INVALID);
    $command->expectsQuestion('Which feature do you want to enable ?', '');
    $command->expectsOutput('A feature name should be given.');
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull();
});

it('throws an error when the subject class doesnt exist', function () {
    $command = artisan('feature:enable', [
        '--feature' => 'client-v2',
        '--model' => 'NoClass',
    ]);

    $command->assertExitCode(Command::INVALID);
    $command->expectsOutput('The subject class [NoClass] does not exist.');
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull();
});

it('throws an error when no identifier option is given', function () {
    $command = artisan('feature:enable', [
        '--feature' => 'client-v2',
        '--model' => User::class,
    ]);

    $command->assertExitCode(Command::INVALID);
    $command->expectsOutput(
        'You should give any of these option to identify model subject you want to enable the feature: --id, --uuid or --email.'
    );
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull();
});

it('throws an error when no model was found', function () {
    $command = artisan('feature:enable', [
        '--feature' => 'client-v2',
        '--model' => User::class,
        '--id' => -1,
    ]);

    $command->assertExitCode(Command::INVALID);
    $command->expectsOutput(sprintf(
        'The model %s(%s) was not found.',
        User::class,
        -1,
    ));
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull();
});

it('throws an error when the model is not an instance of featurable', function () {
    /** @var \Sancherie\Feature\Tests\Models\User $user */
    $user = app(UserFactory::class)->create();

    $command = artisan('feature:enable', [
        '--feature' => 'client-v2',
        '--model' => NotFeaturableUser::class,
        '--id' => $user->id,
    ]);

    $command->assertExitCode(Command::INVALID);
    $command->expectsOutput(sprintf(
        'The model %s(%s) should be an instance of %s.',
        NotFeaturableUser::class,
        $user->id,
        Featurable::class,
    ));
    $command->run();
    expect(Feature::getGlobalFeatureStatus('client-v2'))->toBeNull();
});
