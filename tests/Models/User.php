<?php

namespace Sancherie\Feature\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sancherie\Feature\Contracts\Featurable;
use Sancherie\Feature\Helpers\WithFeatures;

class User extends \Illuminate\Foundation\Auth\User implements Featurable
{
    use HasFactory;
    use WithFeatures;

    /**
     * The table related to the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Whether the primary key of the model is incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}
