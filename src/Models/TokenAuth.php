<?php

namespace InternetGuru\LaravelUser\Models;

use Illuminate\Database\Eloquent\Model;

class TokenAuth extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
