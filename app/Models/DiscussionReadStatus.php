<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionReadStatus extends Model
{
    use HasFactory;

    protected $table = 'discussion_read_status';

    protected $fillable = [
        'user_id',
        'discussion_id',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la discussion
     */
    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }
}
