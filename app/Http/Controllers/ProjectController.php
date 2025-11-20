<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use Inertia\Inertia;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpParser\Node\Stmt\TryCatch;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with(['assignee', 'createdBy'])
            ->latest()
            ->get();

        return Inertia::render('projects/ProjectIndex', [
            'projects' => $projects
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('projects/ProjectCreate');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = Project::create([
            ...$validated,
            'created_by' => Auth::user()->id,
            'status' => ProjectStatus::Pending->value
        ]);


        return redirect()
            ->route('projects.index')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Project created successfully'
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {


        $project->load([
            'assignee',
            'createdBy',
            'tasks.assigneeTo',
            'attachments.user',
            'tasks.comments.user' // Add this to load comments with their authors
        ]);


        return Inertia::render('projects/ProjectShow', [
            'project' => $project
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $project->load(['assignee', 'createdBy']);

        return Inertia::render('projects/ProjectEdit', [
            'project' => $project
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:To Do,In Progress,Done',
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.index')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Project updated successfully'
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        try {
            $project->delete();

            return redirect()
                ->route('projects.index')
                ->with('flash', [
                    'type' => 'success',
                    'message' => 'Project deleted successfully'
                ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('projects.index')
                ->with('flash', [
                    'type' => 'error',
                    'message' => 'Error deleting project'
                ]);
        }
    }

    /**
     * Update project status
     */
    public function updateStatus(Request $request, Project $project)
    {

        $validated = $request->validate([
            'status' => Rule::in(ProjectStatus::values()),
        ]);


        $project->update(['status' => $validated['status']]);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Project status updated successfully'
        ]);
    }

    /**
     * Display the project timeline.
     */
    public function timeline(Project $project)
    {
        $project->load(['tasks.assigneeTo']);

        return Inertia::render('projects/ProjectTimeline', [
            'project' => $project
        ]);
    }
}
