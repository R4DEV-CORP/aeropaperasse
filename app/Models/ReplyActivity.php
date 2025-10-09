<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReplyActivity extends Model
{
    use SoftDeletes;

    protected $fillable = ['content', 'user_id', 'activity_comment_id'];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(CommentActivity::class);
    }
}
