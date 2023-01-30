<?php

namespace Sancherie\Feature\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Traits\Macroable;
use Sancherie\Feature\Contracts\Featurable;
use Sancherie\Feature\FeatureService;
use Sancherie\Feature\Models\Feature as FeatureModel;

/**
 * A facade for the feature service.
 *
 * @method static void declare($feature, $enabled = null)
 * @method static void enable(string $feature, ?Featurable $for = null)
 * @method static void revoke(string $feature, ?Featurable $for = null)
 * @method static void disable(string $feature, ?Featurable $for = null)
 * @method static bool|Collection enabled($feature = null, ?Featurable $featurable = null)
 * @method static Collection getEnabledFeatures(?Featurable $featurable = null)
 * @method static bool isEnabled(string $feature, ?Featurable $featurable = null)
 * @method static bool claimable(Featurable|FeatureModel|string|null $feature, Featurable|null $featurable = null)
 * @method static bool isClaimable(FeatureModel|string $feature, Featurable|null $featurable = null)
 * @method static Collection getClaimableFeatures(Featurable|null $featurable = null)
 * @mixin Macroable
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
