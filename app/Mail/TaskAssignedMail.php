<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Task $task,
        public User $assignee
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Task Assignment: {$this->task->name}",
            tags: ['task-assignment'],
            metadata: [
                'task_id' => $this->task->id,
                'project_id' => $this->task->project_id,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-assigned',
            with: [
                'task' => $this->task,
                'notifiable' => $this->assignee,
                'url' => route('tasks.show', $this->task->id)
            ],
        );
    }
}
