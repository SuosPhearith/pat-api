<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Continent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CountrySeeder extends Seeder
{
    public function run()
    {
        $countryData = [
            [
                'name' => 'France',
                'image' => 'static/Contry/Contry/france.png',
                'continent_name' => 'Europe',
                'population' => '67 million',
                'territory' => '551,695 km²',
                'description' => 'France is known for its art, fashion, and gastronomy.',
            ],
            [
                'name' => 'Japan',
                'image' => 'static/Contry/Contry/japan.png',
                'continent_name' => 'Asia',
                'population' => '125 million',
                'territory' => '377,975 km²',
                'description' => 'Japan is known for its technology and culture.',
            ],
            [
                'name' => 'Cambodia',
                'image' => 'static/Contry/Contry/cam.png',
                'continent_name' => 'Asia',
                'population' => '17 million',
                'territory' => '181,035 km²',
                'description' => 'Cambodia is known for its temples and heritage.',
            ],
            [
                'name' => 'Thailand',
                'image' => 'static/Contry/Contry/thai.png',
                'continent_name' => 'Asia',
                'population' => '71.7 million',
                'territory' => '255,559 km²',
                'description' => 'Thailand is known for its beaches and vibrant cities.',
            ],
            [
                'name' => 'Lao',
                'image' => 'static/Contry/Contry/lao.png',
                'continent_name' => 'Asia',
                'population' => '7.665 million',
                'territory' => '230,800 km²',
                'description' => 'Lao is known for its natural landscapes and culture.',
            ],
            [
                'name' => 'Switzeland',
                'image' => 'static/Contry/Contry/swistland.png',
                'continent_name' => 'Europe',
                'population' => '9,060,598 million',
                'territory' => '41,285 km2',
                'description' => 'Switzerland, officially known as the Swiss Confederation, is a landlocked country situated at the crossroads of Central, Western, and Southern Europe',
            ],
        ];

        foreach ($countryData as $data) {
            $continent = Continent::where('name', $data['continent_name'])->first();

            if ($continent) {
                Country::create([
                    'name' => $data['name'],
                    'image' => $data['image'],
                    'continent_id' => $continent->id,
                    'population' => $data['population'],
                    'territory' => $data['territory'],
                    'description' => $data['description'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                echo "Continent '{$data['continent_name']}' not found. Skipping '{$data['name']}'...\n";
            }
        }
    }
}
