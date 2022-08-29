<?php

namespace Sancherie\Feature\Models;

use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The model representing features.
 *
 * @property string $name
 * @property bool $enabled
 * @property bool $direct_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection $users
 */
class Feature extends Model
{
    /**
     * @inheritdoc
     */
    public $incrementing = false;

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'name',
        'enabled',
    ];

    /**
     * @inheritdoc
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Resolve all the requested features, create them if they don't exist.
     *
     * @param string[] $args)
     * @return ModelCollection
     */
    public static function resolveMany(array $args): ModelCollection
    {
        $featuresNames = Collection::wrap($args)->flatten();
        $features = Feature::query()->whereIn('name', $featuresNames)->get();
        $missingFeatures = $featuresNames->diff($features->pluck('name'));

        foreach ($missingFeatures as $missingFeature) {
            $features[] = Feature::query()->create(['name' => $missingFeature]);
        }

        return $features;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $feature) {
            if (is_null($feature->getKey())) {
                $feature->{$feature->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
