<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'discussion_id',
        'message_comment_id',
    ];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function messageComment(): BelongsTo
    {
        return $this->belongsTo(MessageComment::class);
    }
}