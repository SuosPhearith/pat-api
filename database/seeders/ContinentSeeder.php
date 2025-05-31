<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Continent;

class ContinentSeeder extends Seeder
{
    public function run()
    {
        $continents = [
            [
                'name' => 'Africa',
                'code' => 'AF',
                'description' => 'Africa is the second-largest and second-most populous continent.'
            ],
            [
                'name' => 'Asia',
                'code' => 'AS',
                'description' => 'Asia is the largest and most populous continent.'
            ],
            [
                'name' => 'Europe',
                'code' => 'EU',
                'description' => 'Europe is known for its rich history and cultural heritage.'
            ],
            [
                'name' => 'North America',
                'code' => 'NA',
                'description' => 'North America includes countries like the USA, Canada, and Mexico.'
            ],
            [
                'name' => 'South America',
                'code' => 'SA',
                'description' => 'South America is known for the Amazon rainforest and Andes mountains.'
            ],
            [
                'name' => 'Australia',
                'code' => 'AU',
                'description' => 'Australia is a country and a continent known for unique wildlife.'
            ],
            [
                'name' => 'Antarctica',
                'code' => 'AN',
                'description' => 'Antarctica is the southernmost continent, mostly covered in ice.'
            ],
        ];

        foreach ($continents as $continent) {
            Continent::updateOrCreate(
                ['code' => $continent['code']],
                $continent
            );
        }
    }
}
