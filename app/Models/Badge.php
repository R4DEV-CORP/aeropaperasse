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
        'expiry_date',
        'returned_at',
        'return_document',
        'holder_nom',
        'holder_prenom',
        'holder_email',
        'holder_telephone',
        'holder_client',
        'external_request_number',
        'request_date',
        'import_source'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'returned_at' => 'datetime',
        'request_date' => 'date',
    ];

    public function badgeRequest()
    {
        return $this->belongsTo(BadgeRequest::class);
    }
}
