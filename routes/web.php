<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttachmentController;
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

    Route::prefix('tasks')->as('tasks.')->group(function () {
        Route::post('{task}/comments', [TaskController::class, 'addComment'])->name('comments.store');
        Route::post('{task}/update-status', [TaskController::class, 'updateStatus'])->name('update-status');
        Route::put('{task}/comments/{id}', [TaskController::class, 'updateComment'])->name('comments.update');
        Route::delete('{task}/comments/{id}', [TaskController::class, 'deleteComment'])->name('comments.destroy');
        Route::put('{task}/assign', [TaskController::class, 'assignUser'])->name('assign');
        Route::put('{task}/remove-assign', [TaskController::class, 'removeAssignUser'])->name('remove-assign');
        Route::post('{task}/attachments', [TaskController::class, 'addAttachments'])->name('attachments.store');
        Route::get('board', [TaskController::class, 'board'])->name('board');
    });
    Route::resource('tasks', TaskController::class);
    Route::put('tasks/{task}/status', [TaskController::class, 'updateStatus'])
        ->name('tasks.update-status');


    Route::resource('attachments', AttachmentController::class);

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::prefix('users')->as('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/search/{term}', [UserController::class, 'search'])->name('search');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
