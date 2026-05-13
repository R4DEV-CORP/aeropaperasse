<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoworkerTraining extends Model
{
    use HasFactory;

    protected $fillable = ['coworker_id', 'training_id', 'airport', 'started_at', 'expires_at', 'certificate_path'];

    protected $casts = [
        'started_at' => 'date',
        'expires_at' => 'date',
    ];

    public function coworker(): BelongsTo
    {
        return $this->belongsTo(Coworker::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}
