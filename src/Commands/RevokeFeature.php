<?php

namespace Sancherie\Feature\Commands;

use Illuminate\Console\Command;

class RevokeFeature extends Command
{
    use ToggleFeature;

    /**
     * @inheritdoc
     */
    public $signature = 'feature:revoke
        {--force : Dont ask for confirmation}
        {--feature= : The name of the feature you want to give}
        {--model= : The model class of the subject you want to give the feature}
        {--id= : The ID of the subject you want to give the feature}
        {--email= : The email of the subject you want to give the feature}
    ';

    /**
     * @inheritdoc
     */
    public $description = 'Revoke a feature flag';

    /**
     * @inheritDoc
     */
    protected function toggleValue(): ?bool
    {
        return null;
    }
}
