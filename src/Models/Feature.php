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
 * @property bool $claimable
 * @property int $max_claims
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property-read int $claims_count
 *
 * @property-read ModelCollection $users
 * @property-read ModelCollection<FeatureClaim> $claim
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
    public $keyType = 'string';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'name',
        'enabled',
        'claimable',
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
        'claimable' => 'boolean',
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
    public function claims(): HasMany
    {
        return $this->hasMany(FeatureClaim::class)->whereNotNull('claimed_at');
    }
}
