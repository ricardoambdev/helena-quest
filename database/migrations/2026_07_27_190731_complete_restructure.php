<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. team_progress: drop proof_id, add bonus_onus_ids
        if (Schema::hasColumn('team_progress', 'proof_id')) {
            Schema::table('team_progress', function (Blueprint $table) {
                try { $table->dropForeign(['proof_id']); } catch (\Exception $e) {}
                $table->dropColumn('proof_id');
            });
        }
        if (!Schema::hasColumn('team_progress', 'bonus_onus_ids')) {
            Schema::table('team_progress', function (Blueprint $table) {
                $table->json('bonus_onus_ids')->nullable()->after('hints_bought');
            });
        }

        // 2. teams: war cry
        if (!Schema::hasColumn('teams', 'war_cry_audio_path')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->string('war_cry_audio_path')->nullable()->after('crest_path');
                $table->text('war_cry_text')->nullable()->after('war_cry_audio_path');
            });
        }

        // 3. competitions: school config
        if (!Schema::hasColumn('competitions', 'school_name')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->string('school_name')->nullable()->after('rules_markdown');
                $table->string('school_address')->nullable()->after('school_name');
                $table->decimal('school_latitude', 10, 8)->nullable()->after('school_address');
                $table->decimal('school_longitude', 11, 8)->nullable()->after('school_latitude');
                $table->string('school_logo_path')->nullable()->after('school_longitude');
            });
        }

        // 4. Create bonus_onus
        if (!Schema::hasTable('bonus_onus')) {
            Schema::create('bonus_onus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
                $table->string('type', 20);
                $table->uuid('qr_code_uuid')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('points')->default(0);
                $table->text('message')->nullable();
                $table->text('riddle')->nullable();
                $table->string('correct_answer', 8)->nullable();
                $table->string('image_path')->nullable();
                $table->timestamps();
            });
        }

        // 5. Create team_bonus_onus
        if (!Schema::hasTable('team_bonus_onus')) {
            Schema::create('team_bonus_onus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bonus_onus_id')->constrained('bonus_onus')->cascadeOnDelete();
                $table->string('status', 20)->default('pending');
                $table->integer('score_earned')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['team_id', 'bonus_onus_id']);
            });
        }

        // 6. Drop old tables
        Schema::dropIfExists('team_final_enigma_letters');
        Schema::dropIfExists('team_final_enigma_attempts');
        Schema::dropIfExists('final_enigma_qr_codes');
        Schema::dropIfExists('final_enigmas');
        Schema::dropIfExists('proofs');
    }

    public function down(): void {}
};
