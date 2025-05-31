<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | Create User Type: Admin & Staff
        |--------------------------------------------------------------------------
        */
        DB::table('users_type')->insert([
            ['name' => 'Admin'],
            ['name' => 'Guest'],
        ]);
        /*
        |--------------------------------------------------------------------------
        | Create User
        |--------------------------------------------------------------------------
        */
        $users =  [
            [
                'type_id' => 1,
                'email' => 'roeunsophat123@gmail.com',
                'phone' => '060486849',
                'password' => bcrypt('123456'),
                'is_active' => 1,
                'name' => 'Sophat Roeun',
                'avatar' => 'static/icon/patt.png',
                'created_at' => Carbon::now(),
            ],
            [
                'type_id' => 2,
                'email' => 'nita123@gmail.com',
                'phone' => '097779688',
                'password' => bcrypt('123456'),
                'is_active' => 1,
                'name' => 'Nita Navath',
                'avatar' => 'static/icon/user.png',
                'created_at' => Carbon::now(),
            ],
        ];

        // ✅ Use delete() instead of truncate()
        DB::table('user')->delete();
        DB::table('user')->insert($users);
    }
}
