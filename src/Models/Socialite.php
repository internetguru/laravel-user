<?php

namespace InternetGuru\LaravelUser\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Socialite extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'provider' => User::providers(),
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
