<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        $tasks = Task::all();

        return Inertia::render('Calendar', [
            'projects' => $projects,
            'tasks' => $tasks,
        ]);
    }
}
