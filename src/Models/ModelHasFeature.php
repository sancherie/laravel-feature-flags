<?php

namespace Sancherie\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

/**
 * The model representing pivot between featurable and features.
 *
 * @property int $id
 * @property string $feature_id
 * @property string $featurable_type
 * @property string $featurable_id
 * @property string $enabled
 */
class ModelHasFeature extends Model
{
    use AsPivot;

    /**
     * @inheritdoc
     */
    protected $fillable = [
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
}
