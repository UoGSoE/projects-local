<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ResearchAreaFactory extends Factory
{
    protected $model = \App\ResearchArea::class;

    public function definition()
    {
        return [
            'title' => $this->faker->text(15),
        ];
    }
}
