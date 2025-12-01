<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Pest\Support\Str;
use App\Models\Project;
use App\Models\BoardStage;
use App\Enums\ProjectStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\BoardStagesDefault;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use SebastianBergmann\Environment\Console;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = Project::with(['assignee'])->get(); // adjust pagination if needed

        // load BoardStage rows for Project board (morph)
        $stages = BoardStage::where('boardable_type', Project::class)
            ->orderBy('position', 'asc')
            ->get();




        return Inertia::render('projects/ProjectIndex', [
            'projects' => $projects,
            'stages' => $stages,
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
        ]);



        foreach (BoardStagesDefault::cases() as $stage) {
            $position = 0;
            $project->boardStages()->create([
                'name' => $stage->value,
                'color' => '#' . Str::random(6),
                'position' => $position,
                'type' => 'project',
                'boardable_type' => Project::class,
                'boardable_id' => $project->id,
            ]);


            $position++;
        }


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
            'tasks.comments.user',
            'boardStages',
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
            'status' => 'required',
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
            'status' => 'required',
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

    /**
     * Store a new board stage for projects.
     * 'type' will be set to "project" and boardable_type to the Project model class.
     */
    public function storeStage($id, Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'color'    => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'integer'],
        ]);


        // compute position if not provided
        $maxPos = BoardStage::where('boardable_type', Project::class)->max('position');
        $position = $validated['position'] ?? ($maxPos === null ? 0 : $maxPos + 1);

        $stage = BoardStage::create([
            'boardable_type' => Project::class,
            'boardable_id'   => $id,           // global stage for projects
            'name'            => $validated['name'],
            'color'           => $validated['color'] ?? null,
            'position'        => $position,
            'type'            => 'project',     // ensure type is project
        ]);

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => "Stage '{$stage->name}' created.",
        ]);
    }

    /**
     * Update a board stage (project type).
     */
    public function updateBoardStage(Request $request, BoardStage $stage)
    {
        // ensure this stage is for projects
        if ($stage->boardable_type !== Project::class && ($stage->type ?? '') !== 'project') {
            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'Stage not editable.',
            ]);
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'color'    => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'integer'],
        ]);

        $stage->update([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? $stage->color,
            'position' => $validated['position'] ?? $stage->position,
        ]);

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => "Stage '{$stage->name}' updated.",
        ]);
    }

    /**
     * Delete a board stage. Projects referencing it will be unassigned (board_stage_id = null).
     */
    public function destroyBoardStage(BoardStage $stage)
    {
        if ($stage->boardable_type !== Project::class && ($stage->type ?? '') !== 'project') {
            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'Stage not removable.',
            ]);
        }

        // detach projects referencing this stage
        \App\Models\Project::where('board_stage_id', $stage->id)->update(['board_stage_id' => null]);

        $name = $stage->name;
        $stage->delete();

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => "Stage '{$name}' deleted. Projects moved to no stage.",
        ]);
    }

    // update a project's board_stage_id
    public function updateStage(Request $request, Project $project)
    {
        $validated = $request->validate([
            'board_stage_id' => ['nullable', 'integer', 'exists:board_stages,id'],
        ]);

        $project->board_stage_id = $validated['board_stage_id'] ?? null;
        $project->save();

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => 'Project stage updated.',
        ]);
    }
}
