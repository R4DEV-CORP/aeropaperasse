<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Badge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'badge_request_id',
        'status',
        'previous_status',
        'expiry_date',
        'returned_at',
        'return_document',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'returned_at' => 'datetime',
    ];

    public function badgeRequest()
    {
        return $this->belongsTo(BadgeRequest::class);
    }
}
