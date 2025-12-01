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




    Route::prefix('projects/{project}')->group(function () {
        // PUT /projects/{project}/status -> updates project status
        Route::put('/status', [ProjectController::class, 'updateStatus'])
            ->name('projects.update-status');

        Route::prefix('board-stages')->group(function () {
            // POST /projects/{project}/board-stages -> creates a new board stage
            Route::post('/', [ProjectController::class, 'storeStage'])
                ->name('projects.board-stages.store');

            // PUT /projects/{project}/board-stages/{stage} -> updates a specific board stage
            // Route::put('/{stage}', [ProjectController::class, 'updateStage'])
            //     ->name('projects.board-stages.update');

            // // DELETE /projects/{project}/board-stages/{stage} -> deletes a specific board stage
            // Route::delete('/{stage}', [ProjectController::class, 'destroyStage'])
            //     ->name('projects.board-stages.destroy');
        });

        // GET /projects/{project}/timeline -> views the project timeline
        Route::get('/timeline', [ProjectController::class, 'timeline'])
            ->name('projects.timeline');

        // Group for Attachments within a specific project
        Route::prefix('attachments')->group(function () {
            // POST /projects/{project}/attachments -> stores a new attachment
            Route::post('/', [ProjectAttachmentController::class, 'store'])
                ->name('projects.attachments.store');

            // GET /projects/{project}/attachments/{attachment}/download -> downloads a specific attachment
            Route::get('/{attachment}/download', [ProjectAttachmentController::class, 'download'])
                ->name('projects.attachments.download');

            // DELETE /projects/{project}/attachments/{attachment} -> deletes a specific attachment
            Route::delete('/{attachment}', [ProjectAttachmentController::class, 'destroy'])
                ->name('projects.attachments.destroy');
        });
    });

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
        Route::post('/board-stages', [UserController::class, 'addStage'])->name('board-stages.store');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
