<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'role',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getRoleLabelAttribute()
    {
        return match($this->role) {
            'safety' => 'Référent sûreté',
            'security' => 'Correspondant sécurité',
            'hr' => 'Contact RH',
            'manager' => 'Gestionnaire',
        };
    }
}
