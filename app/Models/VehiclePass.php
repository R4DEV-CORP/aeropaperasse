<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePass extends Model
{
    use HasFactory;

    protected $table = 'vehicle_passes';

    protected $fillable = [
        'created_by',
        'client_id',
        'status',
        'previous_status',
        'reject_reason',
        'certificate_of_registration',
        'company_stamp',
        'airport',
        'plate_number',
        'car_brand',
    ];

    protected $casts = [
        'pending' => 'datetime',    
        'rejected' => 'datetime',
        'approved' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
