<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Badge;

class BadgeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'status',
        'reject_reason',
        'photoIdentite',
        'pieceIdentite',
        'autorisationActivite',
        'certificatFormation',
        'attestationFormation',
        'est_habilitation',
        'documentFor',
        'facture',
        'airport',
        'client_id',
        'created_by',
        'draft_at',
        'pending_rem_at',
        'rejected_rem_at',
        'pending_adp_at',
        'approved_adp_at',
        'rejected_adp_at',
        'pending_fabrication_at',
        'ready_for_delivery_at'
    ];

    protected $casts = [
        'est_habilitation' => 'boolean',
        'attestationFormation' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'draft_at' => 'datetime',
        'pending_rem_at' => 'datetime',
        'rejected_rem_at' => 'datetime',
        'pending_adp_at' => 'datetime',
        'approved_adp_at' => 'datetime',
        'rejected_adp_at' => 'datetime',
        'pending_fabrication_at' => 'datetime',
        'ready_for_delivery_at' => 'datetime'
    ];

    // Scope local pour filtrer les demandes d'un utilisateur
    public function scopeForUser($query, User $user)
    {
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }
        return $query;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
}
