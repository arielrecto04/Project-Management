<?php

namespace App\Models;

use App\Notifications\TaskAssigned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskAssignedMail;

class Task extends Model
{
    protected $fillable = [
        'name',
        'description',
        'project_id',
        'assignee_to',
        'due_date',
        'status',
        'created_by'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assigneeTo()
    {
        return $this->belongsTo(User::class, 'assignee_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::created(function ($task) {
            if ($task->assignee_to) {
                $assignee = User::find($task->assignee_to);
                Mail::to($assignee->email)->send(new TaskAssignedMail($task, $assignee));
            }
        });

        static::updated(function ($task) {
            if ($task->isDirty('assignee_to') && $task->assignee_to) {
                $assignee = User::find($task->assignee_to);
                Mail::to($assignee->email)->send(new TaskAssignedMail($task, $assignee));
            }
        });
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
