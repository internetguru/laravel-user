<?php

namespace InternetGuru\LaravelUser\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $email
 * @property Carbon $expires_at
 * @property Carbon $updated_at
 */
class PinLogin extends Model
{
    /** Carries the address from the sign-in form to the PIN check, so it never appears in a URL. */
    public const SESSION_KEY = 'pin_login.email';

    protected $fillable = [
        'user_id',
        'email',
        'pin',
        'expires_at',
        'remember',
        'register',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'remember' => 'boolean',
            'register' => 'boolean',
        ];
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : Str::lower($value),
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
