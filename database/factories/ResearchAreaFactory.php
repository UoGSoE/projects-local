<?php

use App\ResearchArea;
use Faker\Generator as Faker;

$factory->define(ResearchArea::class, function (Faker $faker) {
    return [
        'title' => $faker->text(15),
    ];
});
