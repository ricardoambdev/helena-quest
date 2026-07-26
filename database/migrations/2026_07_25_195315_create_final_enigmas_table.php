<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_enigmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->string('word');
            $table->unsignedSmallInteger('max_attempts')->default(3);
            $table->unsignedInteger('cooldown_minutes')->default(120);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique('competition_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_enigmas');
    }
};
