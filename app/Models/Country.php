<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'continent_id',
        'population',
        'territory',
        'description'
    ];

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}