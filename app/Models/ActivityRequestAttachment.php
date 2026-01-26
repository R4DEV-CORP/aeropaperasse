<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityRequestAttachment extends Model
{
    use HasFactory;

    /**
     * Types de documents disponibles
     */
    public const TYPE_AAO_REQUEST = 'aao_request';

    public const TYPE_KBIS = 'kbis';

    public const TYPE_PRINCIPALS = 'principals';

    public const TYPE_SAFETY_REFERENT = 'safety_referent';

    public const TYPE_SECURITY_REFERENT = 'security_referent';

    public const TYPE_CTA = 'cta';

    protected $fillable = [
        'activity_request_id',
        'path',
        'type',
        'name',
    ];

    public function activityRequest(): BelongsTo
    {
        return $this->belongsTo(ActivityRequest::class);
    }

    /**
     * Scope pour filtrer par type de document
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Vérifie si le document est de type principals (peut être multiple)
     */
    public function isPrincipals(): bool
    {
        return $this->type === self::TYPE_PRINCIPALS;
    }
}
