<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index()
    {
        $projects = Project::select(['id', 'name', 'start_date', 'end_date', 'status'])->get();
        $tasks = Task::select(['id', 'name', 'start_date', 'end_date', 'status'])->get();

        return Inertia::render('Calendar', [
            'projects' => $projects,
            'tasks' => $tasks,
        ]);
    }
}
