<?php

namespace InternetGuru\LaravelUser\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Traits\SocialiteAuth;
use InternetGuru\LaravelUser\Traits\TokenAuth;

// Authenticatable handles remember tokens
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SocialiteAuth;
    use TokenAuth;

    protected $fillable = [
        'name',
        'email',
        'role',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => Role::class,
        ];
    }
}
