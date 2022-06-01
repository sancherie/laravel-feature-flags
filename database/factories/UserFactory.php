<?php

namespace Sancherie\Feature\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sancherie\Feature\Tests\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'email' => $this->faker->email,
        ];
    }
}
