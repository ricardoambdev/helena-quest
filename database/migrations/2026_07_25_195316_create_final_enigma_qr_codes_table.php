<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_enigma_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('final_enigma_id')->constrained('final_enigmas')->cascadeOnDelete();
            $table->uuid('qr_code_uuid')->unique();
            $table->string('letter', 1);
            $table->text('hint_text')->nullable();
            $table->unsignedInteger('order');
            $table->timestamps();

            $table->unique(['final_enigma_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_enigma_qr_codes');
    }
};
