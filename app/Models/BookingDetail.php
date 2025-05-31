<?php

// app/Models/BookingDetail.php
// app/Models/BookingDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;
use App\Models\City;
use App\Models\Trip;

class BookingDetail extends Model
{
    use HasFactory;

    protected $table = 'booking_details';

    protected $fillable = [
        'booking_id',
        'city_id',
        'trip_id', // Add trip_id to the fillable array
        'trip_days',
        'price',
        'num_of_guests',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function trip()  // Add relationship to the Trip model
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }
}
