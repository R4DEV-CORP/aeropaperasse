<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['original_name', 'mime_type', 'file_path', 'message_id', 'file_size'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
