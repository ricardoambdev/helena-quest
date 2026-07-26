<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->string('audio_path');
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['stage_id', 'sent_at']);
            $table->index(['team_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audios');
    }
};
