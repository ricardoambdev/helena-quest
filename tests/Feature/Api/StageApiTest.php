<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Competition;
use App\Models\Hint;
use App\Models\Proof;
use App\Models\Stage;
use App\Models\Team;
use App\Models\TeamStageProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StageApiTest extends TestCase
{
    use RefreshDatabase;

    private Team $team;
    private Stage $stage;
    private Proof $proof;
    private Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->competition = Competition::factory()->create(['status' => 'ongoing']);
        $this->team = Team::factory()->create(['competition_id' => $this->competition->id, 'status' => 'active']);
        $this->proof = Proof::factory()->create(['competition_id' => $this->competition->id]);
        $this->stage = Stage::factory()->create([
            'proof_id' => $this->proof->id,
            'correct_answer' => '12345',
            'secret_number' => '9999',
            'qr_code_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'latitude' => -23.550520,
            'longitude' => -46.633309,
            'radius' => 30,
            'score' => 100,
            'order' => 1,
        ]);
    }

    public function test_current_stage_requires_auth(): void
    {
        $response = $this->getJson('/api/stages/current');
        $response->assertUnauthorized();
    }

    public function test_current_stage_returns_stage(): void
    {
        TeamStageProgress::create([
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'active',
        ]);

        \App\Models\TeamProgress::create([
            'team_id' => $this->team->id,
            'proof_id' => $this->proof->id,
            'current_stage_id' => $this->stage->id,
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->team, 'sanctum')->getJson('/api/stages/current');

        $response->assertOk();
        $response->assertJsonPath('has_stage', true);
        $response->assertJsonPath('stage.name', $this->stage->name);
        $response->assertJsonStructure([
            'stage' => ['id', 'name', 'hints'],
        ]);
    }

    public function test_validate_qr_success(): void
    {
        $response = $this->actingAs($this->team, 'sanctum')->postJson("/api/stages/{$this->stage->id}/validate-qr", [
            'qr_code_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'latitude' => -23.550520,
            'longitude' => -46.633309,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
    }

    public function test_validate_qr_invalid(): void
    {
        $response = $this->actingAs($this->team, 'sanctum')->postJson("/api/stages/{$this->stage->id}/validate-qr", [
            'qr_code_uuid' => '00000000-0000-0000-0000-000000000000',
            'latitude' => -23.550520,
            'longitude' => -46.633309,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error', 'QRCODE_INVALID');
    }

    public function test_answer_correct(): void
    {
        TeamStageProgress::create([
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->team, 'sanctum')->postJson("/api/stages/{$this->stage->id}/answer", [
            'answer' => '12345',
        ]);

        $response->assertOk();
        $response->assertJsonPath('correct', true);
        $response->assertJsonPath('secret_number', '9999');
    }

    public function test_hints_list(): void
    {
        Hint::factory()->create([
            'stage_id' => $this->stage->id,
            'hint_text' => 'Procure perto do sino.',
            'price' => 25,
        ]);

        TeamStageProgress::create([
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->team, 'sanctum')->getJson("/api/stages/{$this->stage->id}/hints");

        $response->assertOk();
        $response->assertJsonCount(1, 'hints');
        $response->assertJsonPath('hints.0.price', 25);
        $response->assertJsonPath('hints.0.locked', true);
    }

    public function test_hints_revealed_after_buy(): void
    {
        $hint = Hint::factory()->create([
            'stage_id' => $this->stage->id,
            'hint_text' => 'Procure perto do sino.',
            'price' => 25,
        ]);

        TeamStageProgress::create([
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'active',
            'hint_used' => true,
        ]);

        $response = $this->actingAs($this->team, 'sanctum')->getJson("/api/stages/{$this->stage->id}/hints");

        $response->assertOk();
        $response->assertJsonPath('hints.0.locked', false);
        $response->assertJsonPath('hints.0.text', 'Procure perto do sino.');
    }

    public function test_send_photo(): void
    {
        TeamStageProgress::create([
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('foto.jpg', 400, 400);

        $response = $this->actingAs($this->team, 'sanctum')->postJson("/api/stages/{$this->stage->id}/send-photo", [
            'photo' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('status', 'photo_sent');
    }

    public function test_competition_status_endpoint(): void
    {
        $response = $this->actingAs($this->team, 'sanctum')->getJson('/api/public/competition/' . $this->competition->id);

        $response->assertOk();
        $response->assertJsonStructure([
            'id', 'name', 'status', 'teams', 'proofs',
        ]);
        $response->assertJsonPath('name', $this->competition->name);
        $response->assertJsonPath('status', $this->competition->status);
    }
}
