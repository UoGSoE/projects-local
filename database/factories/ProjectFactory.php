<?php

use Faker\Generator as Faker;

$factory->define(App\Project::class, function (Faker $faker) {
    return [
        'title' => $faker->text(50),
        'category' => 'undergrad',
        'pre_req' => $faker->text(30),
        'description' => $faker->text(30),
        'max_students' => $faker->numberBetween(1, 5),
        'staff_id' => function () {
            return factory(App\User::class)->create(['is_staff' => true])->id;
        },
        'is_active' => true,
    ];
});
