<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('order');
            $table->enum('status', ['configuring', 'active', 'inactive', 'finished'])->default('configuring');
            $table->string('icon')->nullable();
            $table->string('image_path')->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->unsignedInteger('max_score')->nullable();
            $table->unsignedInteger('estimated_time_minutes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['competition_id', 'name']);
            $table->unique(['competition_id', 'order']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proofs');
    }
};
