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

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_trainings')
            ->withPivot(['id', 'started_at', 'expires_at', 'certificate_path', 'validity_years'])
            ->withTimestamps();
    }
}
