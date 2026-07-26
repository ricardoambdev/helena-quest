<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proof_id')->constrained('proofs')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('order');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedSmallInteger('radius')->default(30);
            $table->uuid('qr_code_uuid')->unique();
            $table->text('narrative_text');
            $table->string('image_path')->nullable();
            $table->string('correct_answer', 8);
            $table->string('secret_number', 8);
            $table->text('next_stage_hint')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->unsignedInteger('penalty')->default(0);
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['proof_id', 'order']);
            $table->index('proof_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
