<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_final_enigma_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('final_enigma_id')->constrained('final_enigmas')->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_number');
            $table->string('guessed_word');
            $table->boolean('correct');
            $table->timestamp('next_available_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'final_enigma_id', 'attempt_number'], 'tfea_team_enigma_attempt_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_final_enigma_attempts');
    }
};
