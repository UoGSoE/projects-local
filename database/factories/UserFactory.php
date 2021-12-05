<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition()
    {
        return [
            'username' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'surname' => preg_replace('/[^a-zA-Z]/', '', $this->faker->lastName),
            'forenames' => preg_replace('/[^a-zA-Z]/', '', $this->faker->firstName),
            'is_staff' => true,
            'is_admin' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function student()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_staff' => false,
                'username' => $this->faker->numberBetween(1000000, 9999999).$this->faker->randomLetter,
                'programme_id' => Programme::factory(),
            ];
        });
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_staff' => true,
                'is_admin' => true,
            ];
        });
    }
}
