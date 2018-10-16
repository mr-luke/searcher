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

$factory->define(Mrluke\Searcher\Tests\Models\Post::class, function (Faker $faker) {

    return [
        'user_id' => \Mrluke\Searcher\Tests\Models\User::inRandomOrder()->first()->id,
        'title' => $faker->sentence(),
        'content' => $faker->text(20000),
    ];
});
