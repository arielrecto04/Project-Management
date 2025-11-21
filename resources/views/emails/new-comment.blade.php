<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #111827;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        }

        .header {
            background: #0ea5a4;
            color: #fff;
            padding: 18px 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .content {
            padding: 20px;
        }

        .meta {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .panel {
            border: 1px solid #e6e9ee;
            border-radius: 6px;
            padding: 14px;
            background: #f8fafc;
            margin: 12px 0;
        }

        .comment-panel {
            background: #ffffff;
            border-color: #e5e7eb;
        }

        .cta {
            display: inline-block;
            background: #0ea5a4;
            color: #fff;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 14px;
        }

        .footer {
            padding: 16px 20px;
            color: #6b7280;
            font-size: 13px;
            border-top: 1px solid #f1f5f9;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        .small {
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="container" role="article" aria-label="New comment notification">
        <div class="header">
            <h1>💬 New comment on a task</h1>
        </div>

        <div class="content">
            <p class="meta">Hello {{ $notifiable->name ?? 'there' }},</p>

            <p class="muted">You have a new comment on the task shown below.</p>

            <div class="panel">
                <strong style="display:block;font-size:15px;margin-bottom:6px;">
                    {{ $task->title ?? ($task->name ?? 'Untitled Task') }}
                </strong>
                @if (!empty($task->project->name))
                    <div class="small">Project: {{ $task->project->name }}</div>
                @endif
                <div class="small" style="margin-top:6px">
                    Commented by: <strong>{{ $commenter->name ?? 'Someone' }}</strong>
                    @if (!empty($comment->created_at))
                        · {{ $comment->created_at->format('M j, Y \a\t g:ia') }}
                    @endif
                </div>
            </div>

            <div class="panel comment-panel">
                {!! nl2br(e(\Illuminate\Support\Str::limit(strip_tags($comment->body ?? ($comment->content ?? '')), 1200))) !!}
            </div>

            <a href="{{ $actionUrl }}" class="cta">View the comment</a>

            <div style="margin-top:14px" class="muted">
                Reply directly in the app to continue the conversation. Adjust notification preferences in your account
                settings if you wish to change alerts.
            </div>
        </div>

        <div class="footer">
            <div class="small">If the button above doesn't work, copy and paste this URL into your browser:</div>
            <div class="small"><a href="{{ $actionUrl }}"
                    style="color:#0ea5a4;text-decoration:none">{{ $actionUrl }}</a></div>
            <div style="margin-top:10px" class="small">Regards, <br>{{ config('app.name') }}</div>
        </div>
    </div>
</body>

</html>
