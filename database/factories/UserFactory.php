<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'username' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'surname' => preg_replace('/[^a-zA-Z]/', '', $faker->lastName),
        'forenames' => preg_replace('/[^a-zA-Z]/', '', $faker->firstName),
        'is_staff' => true,
        'is_admin' => false,
        'remember_token' => str_random(10),
    ];
});

$factory->state(App\User::class, 'student', function ($faker) {
    return [
        'is_staff' => false,
        'username' => $faker->numberBetween(1000000, 9999999) . $faker->randomLetter,
    ];
});
