<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Project;
use App\Enums\TaskStatus;
use App\Models\Attachment;
use Illuminate\Support\Str;
use App\Mail\NewTaskComment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Mail\TaskAssignedMail;
use Illuminate\Validation\Rule;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Comment; // if you have a Comment model

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()
            ->with(['project', 'assigneeTo', 'createdBy'])
            ->where('assignee_to', Auth::id());

        // Add search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('project', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Add status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $tasks = $query->latest()->paginate(12);

        return Inertia::render('tasks/TaskIndex', [
            'tasks' => $tasks
        ]);
    }

    public function create(Request $request)
    {
        $projects = Project::with('boardStages')->get();
        $users = User::select('id', 'name')->get();

        return Inertia::render('tasks/TaskCreate', [
            'projects' => $projects,
            'users' => $users,
            'project_id' => $request->query('project_id')
        ]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'assignee_to' => 'nullable|exists:users,id',
            'due_date' => 'required|date',
            'status' => 'required'
        ]);

        $task = Task::create([
            ...$validated,
            'created_by' => Auth::user()->id
        ]);

        if ($task->assignee_to) {
            $assignee = User::findOrFail($task->assignee_to);
            Mail::to($assignee->email)->send(new TaskAssignedMail($task, $assignee));
        }

        return redirect()
            ->route('projects.show', $task->project_id)
            ->with('flash', ['type' => 'success', 'message' => 'Task created successfully']);
    }

    public function show(Task $task)
    {
        $task->load(['project', 'assigneeTo', 'createdBy']);

        return Inertia::render('tasks/TaskShow', [
            'task' => $task
        ]);
    }

    public function edit(Task $task)
    {
        $task->load(['project', 'assigneeTo']);
        $projects = Project::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();

        return Inertia::render('tasks/TaskEdit', [
            'task' => $task,
            'projects' => $projects,
            'users' => $users
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'assignee_to' => 'required|exists:users,id',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        // Check if assignee has changed
        $oldAssignee = $task->assignee_to;
        $task->update($validated);


        return redirect()
            ->route('tasks.index')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Task updated successfully'
            ]);
    }

    public function destroy(Task $task)
    {
        try {
            $task->delete();

            return redirect()
                ->route('tasks.index')
                ->with('flash', [
                    'type' => 'success',
                    'message' => 'Task deleted successfully'
                ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('tasks.index')
                ->with('flash', [
                    'type' => 'error',
                    'message' => 'Error deleting task'
                ]);
        }
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required',
        ]);

        $task->update(['status' => $validated['status']]);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Task status updated successfully'
        ]);
    }


    public function addComment(Request $request, Task $task)
    {

        $commenter = Auth::user();


        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        $commenter = Auth::user();
        $actionUrl = route('tasks.show', $task->id);

        // Notify the task assignee if they are not the commenter
        if ($task->assignee_to !== $commenter->id) {
            $assignee = User::find($task->assignee_to);

            if ($assignee) {
                Mail::to($assignee->email)->send(new NewTaskComment($assignee, $task, $comment, $commenter, $actionUrl));
            }
        }

        // Notify other commenters (excluding the commenter)
        $otherCommenters = $task->comments()
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $commenter->id)
            ->where('user_id', '!=', $task->assignee_to)
            ->pluck('user_id')
            ->unique();



        foreach ($otherCommenters as $userId) {
            $user = User::find($userId);
            if ($user) {
                Mail::to($user->email)->send(new NewTaskComment($user, $task, $comment, $commenter, $actionUrl));
            }
        }

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Comment added successfully',
        ]);
    }

    /**
     * Update a comment on the given task.
     *
     * Only the comment owner may update their comment.
     */
    public function updateComment(Request $request, Task $task, $commentId)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $task->comments()->where('id', $commentId)->firstOrFail();

        if ($comment->user_id !== Auth::id()) {
            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'You are not authorized to update this comment.',
            ])->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $comment->update([
            'body' => $validated['body'],
        ]);

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => 'Comment updated successfully.',
        ]);
    }

    /**
     * Delete a comment from the given task.
     *
     * Only the comment owner may delete their comment.
     */
    public function deleteComment(Request $request, Task $task, $commentId)
    {
        $comment = $task->comments()->where('id', $commentId)->firstOrFail();

        if ($comment->user_id !== Auth::id()) {
            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'You are not authorized to delete this comment.',
            ])->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $comment->delete();

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => 'Comment deleted successfully.',
        ]);
    }

    /**
     * Assign or change assignee for a task.
     *
     * Expects 'assignee_id' in request. Returns JSON with updated task->assignee.
     */
    public function assignUser(Request $request, Task $task)
    {
        $validated = $request->validate([
            'assignee_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        // authorization: ensure current user may assign (adjust policy as needed)
        // example: only project owner or task creator can assign — customize per app rules
        if (!Auth::user()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::findOrFail($validated['assignee_id']);

        // persist assignment
        // assume Task has assignee_id column or relation 'assignee'

        $task->update([
            'assignee_to' => $user->id,
        ]);

        // eager load assignee relation if exists
        if (method_exists($task, 'assignee')) {
            $task->load('assignee');
        }





        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Task assigned successfully'
        ]);
    }

    public function removeAssignUser(Request $request, Task $task)
    {
        // authorization: ensure current user may unassign (adjust policy as needed)
        if (!Auth::user()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // remove assignment
        $task->update([
            'assignee_to' => null,
        ]);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Task unassigned successfully'
        ]);
    }

    public function addAttachments(Request $request, Task $task)
    {

        $request->validate([
            'files.*' => 'required|file|max:10240' // 10MB max per file
        ]);



        foreach ($request->file('files') as $file) {
            $fileName = 'TSK-' . Str::random(40) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs(
                "tasks/attachments",
                $fileName,
                'public' // Changed to public disk
            );


            Attachment::create([
                'name' => $file->getClientOriginalName(),
                'path' =>  asset('/storage/' . $path),
                'type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'user_id' => Auth::user()->id,
                'extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getClientMimeType(),
                'attachable_id' => $task->id,
                'attachable_type' => get_class($task),
                'user_id' => Auth::user()->id,
            ]);
        }

        return redirect()->back();
    }

    // New: board view for user's tasks (grouped by due date)
    public function board(Request $request)
    {
        $query = Task::with(['project', 'assigneeTo', 'comments.user', 'attachments'])
            ->where(function ($q) {
                // show tasks relevant to current user: assigned to or created by or within user's projects
                $q->where('assignee_to', Auth::id())
                    ->orWhere('created_by', Auth::id());
            })->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC");

        // optional filters (status/search) can be passed
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $tasks = $query->get()->map(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'description' => $t->description,
                'due_date' => $t->due_date,
                'start_date' => $t->start_date,
                'status' => $t->status,
                'project' => $t->project ? ['id' => $t->project->id, 'name' => $t->project->name] : null,
                'assigneeTo' => $t->assigneeTo ? ['id' => $t->assigneeTo->id, 'name' => $t->assigneeTo->name] : null,
                'attachments' => $t->attachments->map(function ($a) {
                    return ['id' => $a->id, 'name' => $a->name, 'path' => $a->path, 'type' => $a->type, 'size' => $a->size];
                }),
                'comments' => $t->comments->map(function ($c) {
                    return ['id' => $c->id, 'body' => $c->body, 'user' => $c->user ? ['id' => $c->user->id, 'name' => $c->user->name] : null, 'created_at' => $c->created_at];
                }),
                'created_at' => $t->created_at,
            ];
        })->toArray();

        return Inertia::render('tasks/TaskBoard', [
            'tasks' => $tasks,
            'currentUser' => Auth::user() ? ['id' => Auth::user()->id, 'name' => Auth::user()->name] : null,
        ]);
    }
}
