<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'content',
        'status',
        'user_id',
        'last_comment_user_id'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(DiscussionFile::class);
    }

    public function messageComments(): HasMany
    {
        return $this->hasMany(MessageComment::class);
    }

    public function message_comments(): HasMany
    {
        return $this->hasMany(MessageComment::class);
    }

    public function readStatuses(): HasMany
    {
        return $this->hasMany(DiscussionReadStatus::class);
    }

    /**
     * Vérifie si la discussion est non lue pour un utilisateur spécifique
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool true si la discussion est non lue, false sinon
     */
    public function isUnreadForUser(int $userId): bool
    {
        $readStatus = $this->readStatuses()
            ->where('user_id', $userId)
            ->first();
        
        if (!$readStatus) {
            return true;
        }
        
        $lastComment = $this->messageComments()
            ->latest()
            ->first();
        
        if (!$lastComment) {
            return false;
        }
        
        return $readStatus->last_read_at < $lastComment->created_at;
    }
    
    /**
     * Marque la discussion comme lue pour un utilisateur spécifique
     * 
     * @param int $userId ID de l'utilisateur
     * @return DiscussionReadStatus Le statut de lecture créé ou mis à jour
     */
    public function markAsReadForUser(int $userId): DiscussionReadStatus
    {
        return DiscussionReadStatus::updateOrCreate(
            ['user_id' => $userId, 'discussion_id' => $this->id],
            ['last_read_at' => now()]
        );
    }
}