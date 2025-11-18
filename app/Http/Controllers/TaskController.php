<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Project;
use App\Enums\TaskStatus;
use App\Mail\TaskAssignedMail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
        $projects = Project::select('id', 'name')->get();
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
            'status' => ['required', Rule::in(TaskStatus::values())]
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

        // Send notification if assignee has changed
        if ($validated['assignee_to'] !== $oldAssignee) {
            $assignee = User::find($validated['assignee_to']);
            $assignee->notify(new TaskAssigned($task));
        }

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
            'status' => Rule::in(TaskStatus::values()),
        ]);

        $task->update(['status' => $validated['status']]);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Task status updated successfully'
        ]);
    }
}
