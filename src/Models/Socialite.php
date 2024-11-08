<?php

namespace InternetGuru\LaravelUser\Models;

use Illuminate\Database\Eloquent\Model;
use InternetGuru\LaravelUser\Enums\Provider;

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
