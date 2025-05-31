<?php

// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;
use App\Models\City;
use App\Models\Trip;
use App\Models\BookingDetail;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'name',
        'phone_number',
        'num_of_guests',
        'checkin_date',
        'destination',
        'status',
        'user_id',
        'trip_id',
        'payment',
        'booked_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function details()
    {
        return $this->hasMany(BookingDetail::class, 'booking_id')
                    ->with(['city', 'trip.city']); // Optional deep eager loading
    }

}
