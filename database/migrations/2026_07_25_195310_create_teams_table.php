<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->string('name');
            $table->string('color_hex', 7);
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->enum('status', ['active', 'blocked', 'inactive', 'eliminated'])->default('active');
            $table->string('crest_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('description')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->foreignId('password_changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['competition_id', 'name']);
            $table->index(['status', 'competition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
