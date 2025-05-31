<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends Model
{
    use HasFactory;

    protected $table = 'users_type';

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'type_id');
    }
}