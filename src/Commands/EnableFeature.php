<?php

namespace Sancherie\Feature\Commands;

use Illuminate\Console\Command;

class EnableFeature extends Command
{
    use ToggleFeature;

    /**
     * @inheritdoc
     */
    public $signature = 'feature:enable
        {--force : Dont ask for confirmation}
        {--feature= : The name of the feature you want to give}
        {--model= : The model class of the subject you want to give the feature}
        {--id= : The ID of the subject you want to give the feature}
        {--email= : The email of the subject you want to give the feature}
    ';

    /**
     * @inheritdoc
     */
    public $description = 'Enable a feature flag';

    /**
     * @inheritDoc
     */
    protected function toggleValue(): bool
    {
        return true;
    }
}
