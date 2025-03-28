@component('mail::message')
    # New Task Assignment

    Hello {{ $notifiable->name }},

    You have been assigned to a new task in the project **{{ $task->project->name }}**.

    ## Task Details

    **Name:** {{ $task->name }}
    **Due Date:** {{ $task->due_date?->format('M d, Y') }}
    **Description:** {{ $task->description }}

    @component('mail::button', ['url' => route('tasks.show', $task->id), 'color' => 'orange'])
        View Task Details
    @endcomponent

    Please review the task details and update the status as you progress.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
