<?php

namespace InternetGuru\LaravelSocialite\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use InternetGuru\LaravelSocialite\Enums\Provider;

class Socialite extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'name',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'provider' => Provider::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
