<?php

namespace InternetGuru\LaravelUser\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use InternetGuru\LaravelCommon\Traits\AssociationHistory;
use InternetGuru\LaravelUser\Enums\Provider;
use InternetGuru\LaravelUser\Traits\BaseAuth;
use InternetGuru\LaravelUser\Traits\PinLogin;
use InternetGuru\LaravelUser\Traits\SocialiteAuth;
use Internetguru\ModelBrowser\Traits\HasModelBrowserFilters;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use AssociationHistory;
    use Authenticatable;
    use Authorizable;
    use BaseAuth;
    use HasFactory;
    use HasModelBrowserFilters;
    use Notifiable;
    use PinLogin;
    use SocialiteAuth;

    protected array $associationHistoryTracked = [
        'name',
        'email',
        'phone',
        'role',
        'lang',
    ];

    protected $modelBrowserFilterSessionKey = 'laravel-user-user-filters';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'lang',
        'created_by',
        'logged_at',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => static::roles(),
            'logged_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (! array_key_exists('created_by', $user->getAttributes())) {
                $user->created_by = auth()->id();
            }
        });
    }

    public function getSocialiteAttribute(): string
    {
        $providers = $this->socialites->pluck('provider.value')->sort()->implode(',');

        return $providers ? 'connected:' . $providers : 'disconnected';
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(static::class, 'created_by');
    }

    public function isAutomatic(): bool
    {
        return $this->created_by !== null
            && $this->created_by === $this->id
            && $this->logged_at === null;
    }

    /**
     * Dynamic is{Role}() and is{Role}Plus() checks.
     * E.g. $user->isAdmin(), $user->isOperatorPlus().
     */
    public function __call($method, $parameters)
    {
        if (str_starts_with($method, 'is')) {
            $rolesEnum = static::roles();
            $suffix = substr($method, 2);

            if (str_ends_with($suffix, 'Plus')) {
                $roleName = strtoupper(substr($suffix, 0, -4));
                if (defined("$rolesEnum::$roleName")) {
                    return $this->role->level() >= constant("$rolesEnum::$roleName")->level();
                }
            }

            $roleName = strtoupper($suffix);
            if (defined("$rolesEnum::$roleName")) {
                return $this->role === constant("$rolesEnum::$roleName");
            }
        }

        return parent::__call($method, $parameters);
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
            ->reject(fn ($user) => $user->isAutomatic())
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

    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    public function getPreference(string $key, mixed $default = null): mixed
    {
        $preference = $this->preferences()->where('key', $key)->first();

        return $preference ? $preference->value : $default;
    }

    public function setPreference(string $key, mixed $value): UserPreference
    {
        return $this->preferences()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public function scopeFilterAutomatic($query)
    {
        $table = $this->getTable();

        return $query->where(function ($q) use ($table) {
            $q->whereNull("{$table}.created_by")
                ->orWhereColumn("{$table}.created_by", '!=', "{$table}.id")
                ->orWhereNotNull("{$table}.logged_at");
        });
    }

    public static function summary()
    {
        return static::query()
            ->filterAutomatic()
            ->when(
                ! auth()?->user()?->isAdmin(),
                fn ($query) => $query->where('role', '!=', static::roles()::ADMIN->value)
            );
    }
}
