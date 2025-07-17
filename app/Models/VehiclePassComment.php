<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehiclePassComment extends Model
{
    use SoftDeletes;

    protected $fillable = ['content', 'user_id', 'vehicle_pass_id'];

    protected $with = ['user', 'replies'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehiclePass()
    {
        return $this->belongsTo(VehiclePass::class);
    }

    public function replies()
    {
        return $this->hasMany(VehiclePassReply::class);
    }
}
