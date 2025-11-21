<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewTaskComment extends Mailable
{
    use Queueable, SerializesModels;

    // make these public so the Blade view can access them directly
    public $notifiable;
    public $task;
    public $comment;
    public $commenter;
    public $actionUrl;

    /**
     * Create a new message instance.
     *
     * @param  mixed  $notifiable  Recipient user/model
     * @param  mixed  $task
     * @param  mixed  $comment
     * @param  mixed|null $commenter
     * @param  string|null $actionUrl
     */
    public function __construct($notifiable, $task, $comment, $commenter = null, string $actionUrl = null)
    {
        $this->notifiable = $notifiable;
        $this->task = $task;
        $this->comment = $comment;
        $this->commenter = $commenter;
        $this->actionUrl = $actionUrl ?? route('tasks.show', $task->id ?? null);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $taskTitle = $this->task->title ?? ($this->task->name ?? 'Task');
        return new Envelope(
            subject: "New comment on: {$taskTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-comment',
            with: [
                'notifiable' => $this->notifiable,
                'task' => $this->task,
                'comment' => $this->comment,
                'commenter' => $this->commenter,
                'actionUrl' => $this->actionUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
