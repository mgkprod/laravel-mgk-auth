<?php

namespace MGK\Auth\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAuthToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'provider',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function latestForUser(Authenticatable $user)
    {
        return self::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }
}
