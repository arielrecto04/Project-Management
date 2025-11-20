<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'body',
        'user_id',
        'type'
    ];


    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(){
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function replies(){
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function mentionedUsers(){
        return $this->belongsToMany(User::class, 'comment_mentions', 'comment_id', 'user_id');
    }
}
