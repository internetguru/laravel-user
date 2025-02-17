<?php

namespace InternetGuru\LaravelUser\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use InternetGuru\LaravelUser\Traits\BaseAuth;
use InternetGuru\LaravelUser\Traits\SocialiteAuth;
use InternetGuru\LaravelUser\Traits\TokenAuth;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use BaseAuth;
    use HasFactory;
    use Notifiable;
    use SocialiteAuth;
    use TokenAuth;

    protected $fillable = [
        'name',
        'email',
        'role',
        'lang',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => User::roles(),
        ];
    }

    public static function summary()
    {
        return self::orderBy('name');
    }

    public static function getDemoUsers(): array
    {
        return self::all()
            ->sortBy(fn ($user) => $user->role->level())
            ->map(
                fn ($user) => [
                    'id' => $user->email,
                    'name' => $user->name . ' (' . __('ig-user::user.roles.' . $user->role->value) . ')',
                ]
            )->toArray();
    }
}
