<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectAttachmentController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::resource('projects', ProjectController::class);
    Route::put('/projects/{project}/status', [ProjectController::class, 'updateStatus'])
        ->name('projects.update-status');
    Route::get('/projects/{project}/timeline', [ProjectController::class, 'timeline'])->name('projects.timeline');

    Route::post('/projects/{project}/attachments', [ProjectAttachmentController::class, 'store'])
        ->name('projects.attachments.store');
    Route::get('/projects/{project}/attachments/{attachment}/download', [ProjectAttachmentController::class, 'download'])
        ->name('projects.attachments.download');
    Route::delete('/projects/{project}/attachments/{attachment}', [ProjectAttachmentController::class, 'destroy'])
        ->name('projects.attachments.destroy');

    Route::resource('tasks', TaskController::class);
    Route::put('tasks/{task}/status', [TaskController::class, 'updateStatus'])
        ->name('tasks.update-status');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
