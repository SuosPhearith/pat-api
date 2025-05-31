<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    protected $table = 'trips';

    // Specify which fields are mass assignable
    protected $fillable = [
        'title',
        'description',
        'price',
        'start_date',
        'end_date',
        'city_id',
    ];

    // Define relationships

    /**
     * A trip belongs to a city
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * A trip can have many bookings
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
