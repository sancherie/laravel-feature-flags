<?php

namespace Sancherie\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Sancherie\Feature\Contracts\Featurable;

/**
 * The model representing pivot between featurable and features.
 *
 * @property int $id
 * @property int $uuid
 * @property string $feature_id
 * @property string $featurable_type
 * @property string $featurable_id
 * @property string $enabled
 *
 * @property Featurable $featurable
 */
class ModelHasFeature extends Model
{
    use AsPivot;

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'uuid',
        'feature_id',
        'featurable_type',
        'featurable_id',
        'enabled',
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
     * The relation to the featurable.
     *
     * @return MorphTo
     */
    public function featurable(): MorphTo
    {
        return $this->morphTo();
    }
}
