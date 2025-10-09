<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityComment extends Model
{
    use SoftDeletes;

    protected $fillable = ['content', 'user_id', 'activity_request_id'];

    protected $with = ['user', 'replies'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badgeRequest()
    {
        return $this->belongsTo(BadgeRequest::class);
    }

    public function replies()
    {
        return $this->hasMany(ReplyActivity::class);
    }
}
