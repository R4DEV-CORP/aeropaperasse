<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'referent_name',
        'referent_email',
        'safety_referent_name_1', 'safety_referent_email_1', 'safety_referent_phone_1',
        'safety_referent_name_2', 'safety_referent_email_2', 'safety_referent_phone_2',
        'safety_referent_name_3', 'safety_referent_email_3', 'safety_referent_phone_3',
        'safety_document',
        'security_correspondent_name', 'security_correspondent_email', 'security_correspondent_phone',
        'security_document',
        'kbis_document',
        'hr_contact_name', 'hr_contact_email', 'hr_contact_phone'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function badgeRequests()
    {
        return $this->hasMany(BadgeRequest::class);
    }
}
