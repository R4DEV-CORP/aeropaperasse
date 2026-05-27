<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwoFactorCode extends Model
{
    /**
     * 2FA codes belong to the central user directory and are issued during login
     * (which runs in a tenant context), so the model is pinned to the central
     * connection. See docs/multi-tenant-migration.md (Auth model).
     */
    protected $connection = 'central';

    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
