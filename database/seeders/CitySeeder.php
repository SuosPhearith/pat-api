<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CitySeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | Create Sample Cities
        |--------------------------------------------------------------------------
        */
        $cities = [
            [
                'name' => 'Paris',
                'image' => 'static/City/Cities/download.jpg',
                'country_id' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Tokyo',
                'image' => 'static/City/Cities/download-1.jpg',

                'country_id' => 2,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Osaka',
                'image' => 'static/City/Cities/download-9.jpg',

                'country_id' => 2,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Fukuoka',
                'image' => 'static/City/Cities/download-10.jpg',

                'country_id' => 2,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Nishio',
                'image' => 'static/City/Cities/download-11.jpg',

                'country_id' => 2,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Siem Reab',
                'image' => 'static/City/Cities/download-6.jpg',

                'country_id' => 3,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Koh Rong',
                'image' => 'static/City/Cities/Koh Rong.jpg',

                'country_id' => 3,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Preah Sihanouk',
                'image' => 'static/City/Cities/Preah Sihanouk.jpeg',

                'country_id' => 3,
                'created_at' => Carbon::now(),
            ],


            [
                'name' => 'Bankok',
                'image' => 'static/City/Cities/download-8.jpg',

                'country_id' => 4,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Vieng Chan',
                'image' => 'static/City/Cities/download-7.jpg',

                'country_id' => 5,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Luang Prabang',
                'image' => 'static/City/Cities/Luang Prabang.png',

                'country_id' => 5,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Pakse',
                'image' => 'static/City/Cities/Pakse.jpg',

                'country_id' => 5,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Phatya',
                'image' => 'static/City/Cities/download-8.jpg',

                'country_id' => 4,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Bordeaux',
                'image' => 'static/City/Cities/bordeaux.png',

                'country_id' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Chiang Mai',
                'image' => 'static/City/Cities/Chiang Mai.png',

                'country_id' => 4,
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'Nice',
                'image' => 'static/City/Cities/nice.png',

                'country_id' => 1,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('cities')->insert($cities);
    }
}