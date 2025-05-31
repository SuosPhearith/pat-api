<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ContinentSeeder::class,
            CountrySeeder::class,
            CitySeeder::class,
            TripSeeder::class,
            UserSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
