<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class BadgeRequest extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'created_by',
        'activity_request_id',
        'coworker_id',
        'status',
        'reject_reason',
        'draft_at',
        'pending_rem_at',
        'rejected_rem_at',
        'pending_adp_at',
        'approved_adp_at',
        'rejected_adp_at',
        'pending_fabrication_at',
        'ready_for_delivery_at',
        'terminated_at',
        'selfie_photo',
        'identification_card',
        'activity_authorization',
        'for_document',
        'formation_certificate_document',
        'invoice_document',
        'application_authorization',
        'validate_training',
    ];

    protected $casts = [
        'application_authorization' => 'boolean',
        'validate_training' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'draft_at' => 'datetime',
        'pending_rem_at' => 'datetime',
        'rejected_rem_at' => 'datetime',
        'pending_adp_at' => 'datetime',
        'approved_adp_at' => 'datetime',
        'rejected_adp_at' => 'datetime',
        'pending_fabrication_at' => 'datetime',
        'ready_for_delivery_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'coworkers.firstname' => $this->coworker->firstname ?? '',
            'coworkers.lastname' => $this->coworker->lastname ?? '',
            'coworkers.email' => $this->coworker->email ?? '',
        ];
    }

    // Scope local pour filtrer les demandes d'un utilisateur
    public function scopeForUser($query, User $user)
    {
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public function badge()
    {
        return $this->hasOne(Badge::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activityRequest()
    {
        return $this->belongsTo(ActivityRequest::class);
    }

    public function coworker()
    {
        return $this->belongsTo(Coworker::class);
    }

    public function comments()
    {
        return $this->hasMany(BadgeComment::class);
    }
}
