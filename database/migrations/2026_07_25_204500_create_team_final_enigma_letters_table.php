<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_final_enigma_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('final_enigma_id')->constrained('final_enigmas')->cascadeOnDelete();
            $table->foreignId('final_enigma_qr_code_id')->constrained('final_enigma_qr_codes')->cascadeOnDelete();
            $table->string('letter', 1);
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->unique(['team_id', 'final_enigma_qr_code_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_final_enigma_letters');
    }
};
