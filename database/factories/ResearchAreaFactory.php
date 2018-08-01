<?php

use Faker\Generator as Faker;
use App\ResearchArea;

$factory->define(ResearchArea::class, function (Faker $faker) {
    return [
        'title' => $faker->text(15),
    ];
});
