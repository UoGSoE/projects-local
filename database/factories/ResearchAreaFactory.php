<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchAreaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->text(15),
        ];
    }
}
