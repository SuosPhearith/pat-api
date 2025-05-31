<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';

    public function country(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Country::class, 'country_id');
    }


    public function bookings(): HasMany
    {
        return $this->hasMany(\App\Models\Booking::class, 'city_id');
    }
}