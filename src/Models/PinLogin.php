<?php

namespace InternetGuru\LaravelUser\Models;

use Illuminate\Database\Eloquent\Model;

class PinLogin extends Model
{
    protected $fillable = [
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
