<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'dendreo_id',
        'title',
        'short_title',
        'duration_hours',
        'duration_days',
        'validity_duration',
        'category',
        'parent_category'
    ];

    protected $casts = [
        'validity_duration' => 'integer'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_trainings')
            ->withPivot(['id', 'started_at', 'expires_at', 'certificate_path'])
            ->withTimestamps();
    }
}