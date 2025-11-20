<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount([
            'createdProjects',
            'assignedProjects',
            'tasks',
            'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'completed');
            }
        ])->get();

        return Inertia::render('users/Index', [
            'users' => $users
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'assignedProjects' => function ($query) {
                $query->withCount(['tasks', 'tasks as completed_tasks_count' => function ($q) {
                    $q->where('status', 'completed');
                }]);
            },
            'tasks' => function ($query) {
                $query->with('project')->latest();
            }
        ]);

        return Inertia::render('users/Show', [
            'user' => $user
        ]);
    }

    public function search($term)
    {
        $users = User::where('name', 'like', '%' . $term . '%')
            ->orWhere('email', 'like', '%' . $term . '%')
            ->get();

        return response()->json($users);
    }
}
