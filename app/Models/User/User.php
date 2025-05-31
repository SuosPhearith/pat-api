<?php

namespace App\Models\User;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';
    protected $fillable = [
        'type_id', 'name', 'email', 'password', 'is_active'
    ];

    // JWT Auth required methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the type of this user (e.g., admin or guest)
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    /**
     * Get all bookings made by this user
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->type && $this->type->name === 'admin';
    }

    /**
     * Check if the user is a guest
     */
    public function isGuest(): bool
    {
        return $this->type && $this->type->name === 'guest';
    }
}