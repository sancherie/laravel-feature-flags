<?php

namespace Sancherie\Feature\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Sancherie\Feature\Contracts\Featurable;
use Sancherie\Feature\FeatureService;

/**
 * A facade for the feature service.
 *
 * @method static void declare($feature, $enabled = null)
 * @method static void enable(string $feature, ?Featurable $for = null)
 * @method static void disable(string $feature, ?Featurable $for = null)
 * @method static bool|Collection enabled($feature = null, ?Featurable $featurable = null)
 * @method static Collection getEnabledFeatures(?Featurable $featurable = null)
 * @method static Collection getGloballyEnabledFeatures()
 * @method static Collection getSpecificallyEnabledFeatures(?Featurable $featurable = null)
 * @method static Collection getProgrammaticallyEnabledFeatures(?Featurable $featurable = null)
 * @method static bool isEnabled(string $feature, ?Featurable $featurable = null)
 * @method static bool isGloballyEnabled(string $feature)
 * @method static bool isSpecificallyEnabled(string $feature, ?Featurable $featurable)
 * @method static bool isProgrammaticallyEnabled(string $feature, ?Featurable $featurable)
 * @method static void forgetCache()
 * @mixin \Illuminate\Support\Traits\Macroable
 */
class Feature extends Facade
{
    /**
     * The name of the facade accessor.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return FeatureService::class;
    }
}
