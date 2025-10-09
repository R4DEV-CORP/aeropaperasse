<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'discussion_id',
        'parent_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MessageComment::class, 'parent_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(MessageComment::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DiscussionFile::class, 'message_comment_id');
    }
}
