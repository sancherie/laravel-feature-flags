<?php

namespace Sancherie\Feature\Commands;

use Illuminate\Console\Command;

class DisableFeature extends Command
{
    use ToggleFeature;

    /**
     * @inheritdoc
     */
    public $signature = 'feature:disable
        {--force : Dont ask for confirmation}
        {--feature= : The name of the feature you want to disable}
        {--model= : The model class of the subject you want to revoke the feature}
        {--id= : The ID of the subject you want to revoke the feature}
        {--email= : The email of the subject you want to revoke the feature}
    ';

    /**
     * @inheritdoc
     */
    public $description = 'Disable a feature flag';

    /**
     * @inheritDoc
     */
    protected function toggleValue(): bool
    {
        return false;
    }
}
