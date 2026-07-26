<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authentication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('username_attempted')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('device_id')->nullable();
            $table->string('user_agent')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('action', ['login', 'logout', 'failed', 'session_killed', 'password_change']);
            $table->boolean('success');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['team_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authentication_logs');
    }
};
