<?php

use Faker\Generator as Faker;

$factory->define(App\Course::class, function (Faker $faker) {
    return [
        'code' => 'ENG'.$faker->numberBetween(1000, 5999),
        'title' => $faker->text(30),
        'category' => $faker->randomElement(['undergrad', 'postgrad']),
        'application_deadline' => now()->addMonths(3)->hour(23)->minute(59),
        'allow_staff_accept' => false,
    ];
});
