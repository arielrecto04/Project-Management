<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(
        protected Task $task
    ) {
        $this->afterCommit = true;
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        try {
            return (new MailMessage)
                ->error()
                ->subject("New Task Assignment: {$this->task->name}")
                ->greeting("Hello {$notifiable->name}!")
                ->line("You have been assigned to a new task in the project {$this->task->project->name}.")
                ->line("Task Details:")
                ->line("• Name: {$this->task->name}")
                ->line("• Description: {$this->task->description}")
                ->line("• Due Date: " . $this->task->due_date?->format('M d, Y'))
                ->action('View Task Details', route('tasks.show', $this->task->id))
                ->line('Please review the task and update its status as you progress.');
        } catch (\Exception $e) {
            \Log::error('Failed to create email notification:', [
                'error' => $e->getMessage(),
                'task_id' => $this->task->id,
                'notifiable' => $notifiable->id
            ]);
            throw $e;
        }
    }

    public function toArray($notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'project_name' => $this->task->project->name,
            'due_date' => $this->task->due_date,
            'assigned_by' => $this->task->createdBy->name,
        ];
    }
}
