<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'super_admin'])->default('admin')->after('email');
            $table->string('username')->nullable()->unique()->after('name');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->boolean('is_active')->default(true)->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'username', 'last_login_at', 'is_active']);
        });
    }
};
