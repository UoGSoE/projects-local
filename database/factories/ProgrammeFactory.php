<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProgrammeFactory extends Factory
{
    protected $model = \App\Models\Programme::class;

    public function definition()
    {
        return [
            'title' => $this->faker->text(30),
            'category' => $this->faker->randomElement(['undergrad', 'postgrad']),
        ];
    }
}
