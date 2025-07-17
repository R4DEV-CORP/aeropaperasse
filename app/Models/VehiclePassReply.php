<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehiclePassReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['content', 'user_id', 'vehicle_pass_comment_id'];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(VehiclePassComment::class, 'vehicle_pass_comment_id');
    }
}
