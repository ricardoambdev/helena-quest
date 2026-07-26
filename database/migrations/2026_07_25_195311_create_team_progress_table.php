<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('proof_id')->constrained('proofs')->cascadeOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->unsignedInteger('total_score')->default(0);
            $table->unsignedInteger('total_time_seconds')->default(0);
            $table->unsignedSmallInteger('stages_completed')->default(0);
            $table->unsignedSmallInteger('correct_answers')->default(0);
            $table->unsignedSmallInteger('wrong_answers')->default(0);
            $table->unsignedSmallInteger('photos_count')->default(0);
            $table->unsignedSmallInteger('audios_count')->default(0);
            $table->unsignedSmallInteger('hints_bought')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'proof_id']);
            $table->index('current_stage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_progress');
    }
};
