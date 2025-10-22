<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coworker extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'firstname',
        'lastname',
        'email',
        'phone',
        'has_leave',
        'departure_date',
    ];

    protected $casts = [
        'departure_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badgeRequests()
    {
        return $this->hasMany(BadgeRequest::class);
    }

    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'coworker_trainings')
            ->withPivot(['id', 'started_at', 'expires_at', 'certificate_path']);
    }
}
