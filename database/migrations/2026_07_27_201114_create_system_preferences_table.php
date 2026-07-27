<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string');
            $table->string('label', 200)->nullable();
            $table->string('group', 50)->default('general');
            $table->timestamps();
        });

        DB::table('system_preferences')->insert([
            ['key' => 'school_name', 'value' => null, 'type' => 'string', 'label' => 'Nome da escola', 'group' => 'school', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'school_address', 'value' => null, 'type' => 'string', 'label' => 'Endereço da escola', 'group' => 'school', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'school_latitude', 'value' => null, 'type' => 'float', 'label' => 'Latitude da escola', 'group' => 'school', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'school_longitude', 'value' => null, 'type' => 'float', 'label' => 'Longitude da escola', 'group' => 'school', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'school_logo_path', 'value' => null, 'type' => 'string', 'label' => 'Logo da escola', 'group' => 'school', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'telao_map_zoom', 'value' => '15', 'type' => 'integer', 'label' => 'Zoom padrão do mapa', 'group' => 'telao', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'telao_refresh_seconds', 'value' => '3', 'type' => 'integer', 'label' => 'Intervalo de atualização (s)', 'group' => 'telao', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_preferences');
    }
};
