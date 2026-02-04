<?php

namespace InternetGuru\LaravelUser\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use InternetGuru\LaravelUser\Enums\Provider;
use InternetGuru\LaravelUser\Traits\BaseAuth;
use InternetGuru\LaravelUser\Traits\SocialiteAuth;
use InternetGuru\LaravelUser\Traits\TokenAuth;
use Internetguru\ModelBrowser\Traits\HasModelBrowserFilters;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use BaseAuth;
    use HasFactory;
    use HasModelBrowserFilters;
    use Notifiable;
    use SocialiteAuth;
    use TokenAuth;

    protected $modelBrowserFilterSessionKey = 'laravel-user-user-filters';

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
            'role' => static::roles(),
        ];
    }

    public static function providers(): string
    {
        return Provider::class;
    }

    public function preferredLocale(): string
    {
        return $this->lang;
    }

    public static function publicRolesArray(): array
    {
        return array_filter(
            static::roles()::cases(),
            fn ($role) => $role !== static::roles()::ADMIN
        );
    }

    public static function getDemoUsers(): array
    {
        return self::all()
            ->sortBy(fn ($user) => $user->role->level())
            ->map(
                fn ($user) => [
                    'id' => $user->email,
                    'name' => $user->name . ' (' . $user->role->translation() . ')',
                ]
            )->toArray();
    }

    public static function roleOptions(): array
    {
        return array_map(
            fn ($role) => [
                'id' => $role->value,
                'name' => $role->translation(),
            ],
            static::roles()::cases()
        );
    }

    public function routeNotificationForMail($notification)
    {
        return [$this->email => $this->name];
    }

    public static function summary()
    {
        $filters = (new static)->getModelBrowserFilters();

        return static::query()
            ->when(
                auth()?->user()->role !== static::roles()::ADMIN,
                fn ($query) => $query->where('role', '!=', static::roles()::ADMIN->value)
            )
            ->when($filters->get('name'), fn ($query, $value) => $query->where('name', 'like', "%{$value}%"))
            ->when($filters->get('email'), fn ($query, $value) => $query->where('email', 'like', "%{$value}%"))
            ->when($filters->get('role'), fn ($query, $value) => $query->where('role', $value));
    }
}
