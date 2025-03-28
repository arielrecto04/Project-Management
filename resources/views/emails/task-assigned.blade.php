<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #f97316;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }

        .content {
            padding: 20px;
        }

        .panel {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }

        .task-panel {
            background-color: #fff7ed;
            border-color: #f97316;
        }

        .project-panel {
            background-color: #f8fafc;
            border-color: #64748b;
        }

        .button {
            display: inline-block;
            background-color: #f97316;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }

        .note {
            border-left: 4px solid #f97316;
            padding-left: 15px;
            margin: 20px 0;
            color: #475569;
        }

        .footer {
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }

        .emoji {
            font-size: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0">🎯 New Task Assignment</h1>
        </div>

        <div class="content">
            <p>Dear {{ $notifiable->name }},</p>

            <p>You have been assigned to a new task in the <strong>{{ $task->project->name }}</strong> project.</p>

            <div class="panel task-panel">
                <h2 style="color: #ea580c; margin-top: 0">📋 Task Details</h2>
                <p><strong>Task Name:</strong> {{ $task->name }}</p>
                <p><strong>Description:</strong> {{ $task->description }}</p>
                <p><strong>Due Date:</strong> {{ date('M d Y', strtotime($task->due_date)) }}</p>
                <p><strong>Priority:</strong> {{ ucfirst($task->priority ?? 'Normal') }}</p>
            </div>

            <div class="panel project-panel">
                <h3 style="color: #475569; margin-top: 0">🏢 Project Context</h3>
                <p><strong>Project:</strong> {{ $task->project->name }}</p>
                <p><strong>Status:</strong> {{ ucfirst($task->project->status) }}</p>
                <p><strong>Progress:</strong>
                    {{ $task->project->tasks()->where('status', 'completed')->count() }}/{{ $task->project->tasks()->count() }}
                    tasks completed
                </p>
            </div>

            <center>
                <a href="{{ route('tasks.show', $task->id) }}" class="button">
                    View Task Details
                </a>
            </center>

            <div class="note">
                <p>Please review the task details and update the status as you progress through the work.
                    If you have any questions or need clarification, please don't hesitate to contact the project
                    manager.</p>
            </div>

            <p>Best regards,<br>{{ config('app.name') }}</p>
        </div>

        <div class="footer">
            <p>If you're having trouble clicking the "View Task Details" button, copy and paste this URL into your web
                browser:<br>
                {{ route('tasks.show', $task->id) }}</p>
        </div>
    </div>
</body>

</html>
