<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\BonusOnus;
use App\Models\Competition;
use App\Models\Stage;
use App\Models\Team;
use App\Models\TeamProgress;
use App\Models\TeamStageProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StageApiTest extends TestCase
{
    use RefreshDatabase;

    private Team $team;
    private Stage $stage;
    private Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->competition = Competition::factory()->create(['status' => 'ongoing']);
        $this->team = Team::factory()->create([
            'competition_id' => $this->competition->id,
            'status' => 'active',
        ]);
        $this->stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'stage_type' => 'charada',
            'unlock_password' => '12345',
            'correct_answer' => 'resposta',
            'secret_number' => '9999',
            'score' => 50,
            'order' => 1,
        ]);
    }

    public function test_unlock_stage_success(): void
    {
        Sanctum::actingAs($this->team);

        $response = $this->postJson("/api/stages/{$this->stage->id}/unlock", [
            'code' => '12345',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Etapa desbloqueada!');
        $response->assertJsonStructure(['success', 'message', 'progress']);

        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'active',
        ]);
    }

    public function test_unlock_stage_wrong_password(): void
    {
        Sanctum::actingAs($this->team);

        $response = $this->postJson("/api/stages/{$this->stage->id}/unlock", [
            'code' => 'wrongpass',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Senha incorreta.');
    }

    public function test_unlock_stage_no_password_configured(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'unlock_password' => null,
            'order' => 2,
        ]);

        Sanctum::actingAs($this->team);

        $response = $this->postJson("/api/stages/{$stage->id}/unlock", [
            'code' => 'qualquer',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Esta etapa nao possui senha de desbloqueio.');
    }

    public function test_scan_bonus(): void
    {
        $bonus = BonusOnus::factory()->bonus()->create([
            'stage_id' => $this->stage->id,
        ]);

        TeamProgress::create([
            'team_id' => $this->team->id,
            'competition_id' => $this->competition->id,
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->team);

        $response = $this->postJson('/api/bonus-onus/scan', [
            'uuid' => $bonus->qr_code_uuid,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('bonus_onus.name', $bonus->name);
        $response->assertJsonPath('bonus_onus.points', 50);
        $response->assertJsonPath('bonus_onus.type', 'bonus');
        $response->assertJsonStructure(['success', 'bonus_onus', 'total_score']);
    }

    public function test_scan_onus(): void
    {
        $onus = BonusOnus::factory()->onus()->create([
            'stage_id' => $this->stage->id,
        ]);

        TeamProgress::create([
            'team_id' => $this->team->id,
            'competition_id' => $this->competition->id,
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->team);

        $response = $this->postJson('/api/bonus-onus/scan', [
            'uuid' => $onus->qr_code_uuid,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('bonus_onus.points', -30);
        $response->assertJsonPath('bonus_onus.type', 'onus');
    }

    public function test_scan_bonus_already_collected(): void
    {
        $bonus = BonusOnus::factory()->bonus()->create([
            'stage_id' => $this->stage->id,
        ]);

        TeamProgress::create([
            'team_id' => $this->team->id,
            'competition_id' => $this->competition->id,
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->team);

        $response1 = $this->postJson('/api/bonus-onus/scan', [
            'uuid' => $bonus->qr_code_uuid,
        ]);
        $response1->assertOk();

        $response2 = $this->postJson('/api/bonus-onus/scan', [
            'uuid' => $bonus->qr_code_uuid,
        ]);

        $response2->assertStatus(422);
        $response2->assertJsonPath('success', false);
        $response2->assertJsonPath('message', 'Este bonus/onus ja foi coletado.');
    }

    public function test_unauthenticated_cannot_access(): void
    {
        $response = $this->postJson("/api/stages/{$this->stage->id}/unlock", [
            'code' => '12345',
        ]);

        $response->assertUnauthorized();
    }

    public function test_validate_qr(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'qr_code_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'latitude' => null,
            'longitude' => null,
            'order' => 2,
        ]);

        Sanctum::actingAs($this->team);

        $response = $this->postJson("/api/stages/{$stage->id}/validate-qr", [
            'qr_code_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure(['success', 'progress']);

        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $stage->id,
            'status' => 'active',
        ]);
    }

    public function test_answer_stage(): void
    {
        TeamStageProgress::create([
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->team);

        $response = $this->postJson("/api/stages/{$this->stage->id}/answer", [
            'answer' => 'resposta',
        ]);

        $response->assertOk();
        $response->assertJsonPath('correct', true);
        $response->assertJsonStructure(['correct', 'message', 'score_earned']);
    }
}
