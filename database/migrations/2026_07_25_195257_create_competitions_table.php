<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('year');
            $table->date('date');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', ['planning', 'published', 'ongoing', 'paused', 'finished', 'archived'])->default('planning');
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->longText('rules_markdown')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'year']);
            $table->index(['status', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
