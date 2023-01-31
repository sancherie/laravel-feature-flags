<?php

namespace Sancherie\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Sancherie\Feature\Contracts\Featurable;

/**
 * The model representing pivot between featurable and features.
 *
 * @property int $id
 * @property string $uuid
 * @property string $feature_id
 * @property string $featurable_type
 * @property string $featurable_id
 * @property bool|null $enabled
 * @property Carbon $claimed_at
 *
 * @property Feature $feature
 * @property Featurable $featurable
 */
class FeatureClaim extends Model
{
    protected $primaryKey = 'uuid';

    public $incrementing = false;

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'uuid',
        'feature_id',
        'featurable_type',
        'featurable_id',
        'enabled',
        'claimed_at'
    ];

    /**
     * @inheritdoc
     */
    protected $dates = [
        'claimed_at',
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $feature) {
            if (is_null($feature->getKey())) {
                $feature->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * The relation to the feature.
     *
     * @return BelongsTo
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class, 'feature_id', 'id');
    }

    /**
     * The relation to the featurable.
     *
     * @return MorphTo
     */
    public function featurable(): MorphTo
    {
        return $this->morphTo(
            'featurable',
            null,
            null,
            'uuid',
        );
    }
}
