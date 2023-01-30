<?php

namespace Sancherie\Feature\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The model representing features.
 *
 * @property string $name
 * @property bool $enabled
 * @property bool $direct_enabled
 * @property int $max_claims
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property-read int $claimed_pivots_count
 *
 * @property-read ModelCollection $users
 * @property-read ModelCollection<ModelHasFeature> $pivots
 * @property-read ModelCollection<ModelHasFeature> $claimedPivots
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
        'max_claims',
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

    /**
     * The relation to the pivots.
     *
     * @return HasMany
     */
    public function pivots(): HasMany
    {
        return $this->hasMany(ModelHasFeature::class);
    }

    /**
     * The relation to the pivots that have been claimed.
     *
     * @return HasMany
     */
    public function claimedPivots(): HasMany
    {
        return $this->pivots()->whereNotNull('claimed_at');
    }
}
