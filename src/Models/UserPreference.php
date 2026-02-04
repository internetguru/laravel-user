<?php

namespace InternetGuru\LaravelUser\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
