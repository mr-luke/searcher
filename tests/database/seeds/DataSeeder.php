<?php

use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        factory(Mrluke\Searcher\Tests\Models\User::class, 100)->create();

        factory(Mrluke\Searcher\Tests\Models\Post::class, 50)->create();
    }
}
