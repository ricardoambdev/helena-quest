<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_stage_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('stages')->cascadeOnDelete();
            $table->enum('status', ['locked', 'active', 'photo_sent', 'answered_correct', 'answered_wrong', 'completed'])->default('locked');
            $table->timestamp('qr_scanned_at')->nullable();
            $table->decimal('gps_lat', 10, 8)->nullable();
            $table->decimal('gps_lng', 11, 8)->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('photo_sent_at')->nullable();
            $table->unsignedSmallInteger('attempts_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('score_earned')->default(0);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->string('last_answer', 8)->nullable();
            $table->boolean('was_correct')->nullable();
            $table->boolean('hint_used')->default(false);
            $table->timestamps();

            $table->unique(['team_id', 'stage_id']);
            $table->index(['stage_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_stage_progress');
    }
};
