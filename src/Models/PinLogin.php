<?php

namespace InternetGuru\LaravelUser\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PinLogin extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
