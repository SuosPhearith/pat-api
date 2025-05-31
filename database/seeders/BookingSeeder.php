<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\BookingDetail;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Required data
        $trips = Trip::with('city')->get();
        $users = User::all();

        if ($trips->isEmpty()) {
            $this->command->warn('No trips found. Please seed the trips table first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed the users table first.');
            return;
        }

        for ($i = 1; $i <= 100; $i++) {
            $trip = $trips->random();
            $user = $users->random();
            $bookedAt = Carbon::now()->addDays(rand(1, 30));
            $numGuests = rand(1, 5);

            // Create Booking
            $booking = Booking::create([
                'receipt_number' => $this->generateReceiptNumber(),
                'name' => $user->name,
                'phone_number' => '0123456' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'num_of_guests' => $numGuests,
                'checkin_date' => $trip->start_date,
                'destination' => $trip->city->name ?? 'Unknown City',
                'status' => $this->randomStatus(),
                'user_id' => $user->id,
                'trip_id' => $trip->id,
                'payment' => 'credit card',
                'booked_at' => $bookedAt,
                'created_at' => $bookedAt,
                'updated_at' => $bookedAt,
            ]);

            // Create Booking Detail
            BookingDetail::create([
                'booking_id' => $booking->id,
                'city_id' => $trip->city_id,
                'trip_days' => $trip->trip_days ?? 3, // fallback if null
                'price' => $trip->price,
                'num_of_guests' => $numGuests,
                'created_at' => $bookedAt,
                'updated_at' => $bookedAt,
            ]);
        }
    }

    private function generateReceiptNumber(): string
    {
        do {
            $number = rand(100000, 999999);
            $exists = DB::table('bookings')->where('receipt_number', $number)->exists();
        } while ($exists);

        return (string) $number;
    }

    private function randomStatus(): string
    {
        $statuses = ['paid'];
        return $statuses[array_rand($statuses)];
    }
}
