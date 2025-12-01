<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\BoardStage;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;

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


    public function addStage(Request $request)
    {

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'color'    => ['nullable', 'string', 'max:50'],
        ]);



        // optional: authorize user (adjust policy as needed)
        // $this->authorize('create', BoardStage::class);

        // compute position if not provided
        $maxPos = BoardStage::where('boardable_type', User::class)
            ->where('boardable_id', auth()->user()->id)->max('position');
        $position = $validated['position'] ?? ($maxPos === null ? 0 : $maxPos + 1);

        $stage = BoardStage::create([
            'boardable_type' => User::class,
            'boardable_id'   => auth()->user()->id,           // global stage for projects
            'name'            => $validated['name'],
            'color'           => $validated['color'] ?? null,
            'position'        => $position,
        ]);

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => "Stage '{$stage->name}' created.",
        ]);
    }
}
