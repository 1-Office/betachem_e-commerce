<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'first_name',
    'last_name',
    'email',
    'phone',
    'password',
    'address',
    'city',
    'province',
    'postal_code',
    'role',
    'status',
])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
