<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
    ];

    public function coworkers()
    {
        return $this->belongsToMany(Coworker::class, 'coworker_trainings')
            ->withPivot(['id', 'started_at', 'expires_at', 'certificate_path'])
            ->withTimestamps();
    }
}
