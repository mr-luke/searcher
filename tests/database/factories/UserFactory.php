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

$factory->define(Mrluke\Searcher\Tests\Models\User::class, function (Faker $faker) {

    $gender = (rand(1,100) % 2) ? 'male' : 'female';

    $first = $faker->firstName($gender);
    $last = $faker->lastName($gender);

    return [
        'name' => str_slug($first .' '. $last .' '.str_random(5)),
        'email' => $faker->unique()->safeEmail,

        'country' => 'Poland',
        'city' => $faker->city,
        'address' => $faker->streetName,

        'first' => $first,
        'last' => $last,
        'gender' => $gender,
        'age' => rand(18, 60),
        'job' => $faker->jobTitle,
    ];
});
