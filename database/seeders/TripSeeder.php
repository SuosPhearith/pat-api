<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Trip;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $cities = City::all();

        foreach ($cities as $index => $city) {
            $tripDays = rand(3, 14); // Random trip duration between 3-14 days
            
            Trip::create([
                'title' => 'Trip to ' . $city->name,
                'description' => 'Explore the wonderful city of ' . $city->name,
                'price' => $this->generateRandomPrice(),
                'trip_days' => $tripDays,
                'start_date' => now()->addDays($index * 10),
                'end_date' => now()->addDays(($index * 10) + $tripDays),
                'city_id' => $city->id,
            ]);
        }
    }

    /**
     * Generate a random price between 500 and 5000
     */
    private function generateRandomPrice(): float
    {
        return rand(500, 5000) + (rand(0, 99) / 100); // e.g. 1245.67
    }
}