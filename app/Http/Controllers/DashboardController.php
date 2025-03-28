<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Get project statistics
        $projectStats = Project::select(
            DB::raw('COUNT(*) as total'),
            DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed'),
            DB::raw('COUNT(CASE WHEN status = "in_progress" THEN 1 END) as in_progress')
        )->first();

        // Get projects by status for the chart
        $projectsByStatus = Project::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn($item) => [
                'name' => ucfirst(str_replace('_', ' ', $item->status)),
                'count' => $item->count
            ])
            ->values();

        // Get recent activities
        $recentActivities = collect()
            ->merge(
                Project::with('createdBy')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn($project) => [
                        'id' => "project_{$project->id}",
                        'type' => 'project_created',
                        'description' => "Created project: {$project->name}",
                        'user' => $project->createdBy->name,
                        'date' => $project->created_at->diffForHumans()
                    ])
            )
            ->merge(
                Task::with(['createdBy', 'project'])
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn($task) => [
                        'id' => "task_{$task->id}",
                        'type' => 'task_created',
                        'description' => "Added task to {$task->project->name}: {$task->name}",
                        'user' => $task->createdBy->name,
                        'date' => $task->created_at->diffForHumans()
                    ])
            )
            ->sortByDesc('date')
            ->take(5)
            ->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProjects' => $projectStats->total,
                'totalUsers' => User::count(),
                'completedProjects' => $projectStats->completed,
                'inProgressProjects' => $projectStats->in_progress,
                'projectsByStatus' => $projectsByStatus,
                'recentActivities' => $recentActivities
            ]
        ]);
    }
}
