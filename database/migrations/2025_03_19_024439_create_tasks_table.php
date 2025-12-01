<?php

use App\Models\User;
use App\Models\Project;
use App\Enums\TaskStatus;
use App\Models\BoardStage;
use App\Enums\BoardStagesDefault;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'assignee_to')->nullable()->constrained('users')->cascadeOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status')->default(BoardStagesDefault::TO_DO->value);
            $table->foreignIdFor(User::class, 'created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
