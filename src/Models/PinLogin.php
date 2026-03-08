<?php

namespace InternetGuru\LaravelUser\Models;

use Illuminate\Database\Eloquent\Model;

class PinLogin extends Model
{
    protected $table = 'token_auths';

    protected $fillable = [
        'user_id',
        'pin',
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
