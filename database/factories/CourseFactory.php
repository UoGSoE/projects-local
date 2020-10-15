<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = \App\Models\Course::class;

    public function definition()
    {
        return [
            'code' => 'ENG'.$this->faker->numberBetween(1000, 5999),
            'title' => $this->faker->text(30),
            'category' => $this->faker->randomElement(['undergrad', 'postgrad']),
            'application_deadline' => now()->addMonths(3)->hour(23)->minute(59),
            'allow_staff_accept' => false,
        ];
    }
}
