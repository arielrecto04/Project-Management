<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('board_stages', function (Blueprint $table) {
            $table->id();
            $table->morphs('boardable');
            $table->string('name')->default('New Stage');
            $table->string('color')->default('#FFFFFF');
            $table->integer('position')->default(0);
            $table->string('type')->default('default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_stages');
    }
};
