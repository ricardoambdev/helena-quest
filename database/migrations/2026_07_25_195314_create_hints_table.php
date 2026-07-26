<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('stages')->cascadeOnDelete();
            $table->text('hint_text');
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['stage_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hints');
    }
};
