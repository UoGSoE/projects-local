<?php

use Faker\Generator as Faker;

$factory->define(App\Programme::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
    ];
});
