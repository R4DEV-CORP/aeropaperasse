<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['subject', 'status', 'created_by'];

    // public function messages(): HasMany
    // {
    //     return $this->hasMany(Message::class);
    // }

    // public function participants(): BelongsToMany
    // {
    //     return $this->belongsToMany(User::class, 'conversation_user')
    //         ->withTimestamps();
    // }

    public function lastMessage()
    {
        return $this->messages()->latest()->first();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user', 'conversation_id', 'user_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_user', 'conversation_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
