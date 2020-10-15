<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = \App\Models\Project::class;

    public function definition()
    {
        return [
            'title' => $this->faker->text(50),
            'category' => 'undergrad',
            'pre_req' => $this->faker->text(30),
            'description' => $this->faker->text(30),
            'max_students' => $this->faker->numberBetween(1, 5),
            'staff_id' => \App\Models\User::factory(),
            'is_active' => true,
            'is_confidential' => false,
            'is_placement' => false,
        ];
    }
}
