<?php

namespace Sancherie\Feature\Commands;

use Sancherie\Feature\Contracts\Featurable;
use Sancherie\Feature\Facades\Feature;

trait ToggleFeature
{
    /**
     * The name of the feature.
     *
     * @var string|null
     */
    private ?string $feature;

    /**
     * The identifier of the subject
     *
     * @var string|int
     */
    private $subjectIdentifier = null;

    /**
     * The subject of the feature.
     *
     * @var \Sancherie\Feature\Contracts\Featurable|null
     */
    private ?Featurable $subject = null;

    public function handle(): int
    {
        if (($code = $this->parseFeature()) !== self::SUCCESS) {
            return $code;
        }

        if (($code = $this->parseSubject()) !== self::SUCCESS) {
            return $code;
        }

        $this->toggleFeature();

        return self::SUCCESS;
    }

    private function parseFeature(): int
    {
        $this->feature = $this->option('feature');

        if (is_null($this->feature)) {
            $this->feature = $this->ask("Which feature do you want to {$this->toggleString()} ?");
        }

        if (empty($this->feature)) {
            $this->error('A feature name should be given.');

            return self::INVALID;
        }

        return self::SUCCESS;
    }

    private function parseSubject(): int
    {
        $subjectClass = $this->option('model');

        if (is_null($subjectClass)) {
            return self::SUCCESS;
        }

        if (! class_exists($subjectClass)) {
            $this->error("The subject class [$subjectClass] does not exist.");

            return self::INVALID;
        }

        if (! is_null($id = $this->option('id'))) {
            $subject = $subjectClass::find($id);
        } elseif (! is_null($email = $this->option('email'))) {
            $subject = $subjectClass::whereEmail($email)->first();
        } else {
            $this->error(
                'You should give any of these option to identify model subject you want to enable the feature: --id, --uuid or --email.'
            );

            return self::INVALID;
        }

        $this->subjectIdentifier = $id ?? $uuid ?? $email ?? null;

        if (is_null($subject)) {
            $this->error(sprintf(
                'The model %s(%s) was not found.',
                $subjectClass,
                $this->subjectIdentifier,
            ));

            return self::INVALID;
        }

        if (! $subject instanceof Featurable) {
            $this->error(sprintf(
                'The model %s(%s) should be an instance of %s.',
                $subjectClass,
                $id ?? $uuid ?? $email ?? null,
                Featurable::class
            ));

            return self::INVALID;
        }

        $this->subject = $subject;

        return self::SUCCESS;
    }

    private function toggleFeature(): void
    {
        if (! $this->confirmToggle()) {
            $this->info('Action canceled');

            return;
        }

        if ($this->toggleValue()) {
            Feature::enable($this->feature, $this->subject);
        } else {
            Feature::disable($this->feature, $this->subject);
        }

        $this->writeSuccess();
    }

    private function confirmToggle(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if (is_null($this->subject)) {
            $text = sprintf(
                'You are about to globally %s the feature [%s]. Do you want to continue ?',
                $this->toggleString(),
                $this->feature
            );
        } else {
            $text = sprintf(
                'You are about to %s the feature [%s] for the featurable %s(%s). Do you want to continue ?',
                $this->toggleString(),
                $this->feature,
                get_class($this->subject),
                $this->subjectIdentifier,
            );
        }

        return $this->confirm($text);
    }

    private function writeSuccess(): void
    {
        if (is_null($this->subject)) {
            $text = sprintf(
                'The feature [%s] has been successfully %sd globally !',
                $this->feature,
                $this->toggleString()
            );
        } else {
            $text = sprintf(
                'The feature [%s] has been successfully %sd for %s(%s) !',
                $this->feature,
                $this->toggleString(),
                get_class($this->subject),
                $this->subjectIdentifier,
            );
        }

        $this->info($text);
    }

    private function toggleString(): string
    {
        return $this->toggleValue() ? 'enable' : 'disable';
    }

    /**
     * The value to be given for the toggle.
     *
     * @return bool
     */
    abstract protected function toggleValue(): bool;
}
