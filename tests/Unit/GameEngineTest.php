<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\BonusOnus;
use App\Models\Competition;
use App\Models\Stage;
use App\Models\Team;
use App\Models\TeamProgress;
use App\Models\TeamStageProgress;
use App\Models\Hint;
use App\Services\DistanceCalculator;
use App\Services\GameEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class GameEngineTest extends TestCase
{
    use RefreshDatabase;

    private GameEngine $engine;
    private Competition $competition;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $distance = new DistanceCalculator;
        $this->engine = new GameEngine($distance);
        $this->competition = Competition::factory()->create(['status' => 'ongoing']);
        $this->team = Team::factory()->create(['competition_id' => $this->competition->id, 'status' => 'active']);
    }

    public function test_unlock_with_password_correct(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'unlock_password' => 'segredo',
            'order' => 1,
        ]);

        $result = $this->engine->unlockWithPassword($this->team, $stage, 'segredo');

        $this->assertTrue($result['success']);
        $this->assertEquals('Etapa desbloqueada!', $result['message']);

        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $stage->id,
            'status' => 'active',
        ]);
    }

    public function test_unlock_with_password_wrong(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'unlock_password' => 'segredo',
            'order' => 1,
        ]);

        $result = $this->engine->unlockWithPassword($this->team, $stage, 'errado');

        $this->assertFalse($result['success']);
        $this->assertEquals('Senha incorreta.', $result['message']);

        $this->assertDatabaseMissing('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $stage->id,
        ]);
    }

    public function test_unlock_without_password_configured(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'unlock_password' => null,
            'order' => 1,
        ]);

        $result = $this->engine->unlockWithPassword($this->team, $stage, 'qualquer');

        $this->assertFalse($result['success']);
        $this->assertEquals('Esta etapa nao possui senha de desbloqueio.', $result['message']);
    }

    public function test_validate_qr_and_gps_success(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'qr_code_uuid' => 'abcdef-123456',
            'latitude' => -23.5505,
            'longitude' => -46.6333,
            'radius' => 100,
            'order' => 1,
        ]);

        $result = $this->engine->validateQrAndGps(
            $this->team, $stage, 'abcdef-123456', -23.5505, -46.6333
        );

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('progress', $result);

        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $stage->id,
            'status' => 'active',
        ]);
    }

    public function test_validate_qr_invalid(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'qr_code_uuid' => 'correct-uuid',
            'order' => 1,
        ]);

        $result = $this->engine->validateQrAndGps($this->team, $stage, 'wrong-uuid');

        $this->assertFalse($result['success']);
        $this->assertEquals('QRCODE_INVALID', $result['error']);
    }

    public function test_validate_qr_gps_out_of_range(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'qr_code_uuid' => 'uuid-valid',
            'latitude' => -23.5505,
            'longitude' => -46.6333,
            'radius' => 10,
            'order' => 1,
        ]);

        $result = $this->engine->validateQrAndGps(
            $this->team, $stage, 'uuid-valid', -23.5600, -46.6400
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('GPS_OUT_OF_RANGE', $result['error']);
    }

    public function test_validate_answer_correct(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'correct_answer' => 'resposta',
            'latitude' => null,
            'longitude' => null,
            'order' => 1,
        ]);

        $this->engine->validateQrAndGps($this->team, $stage, $stage->qr_code_uuid);

        $result = $this->engine->validateAnswer($this->team, $stage, 'resposta');

        $this->assertTrue($result['correct']);
        $this->assertEquals('Resposta correta!', $result['message']);

        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $stage->id,
            'status' => 'completed',
            'was_correct' => true,
        ]);
    }

    public function test_validate_answer_wrong(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'correct_answer' => 'resposta',
            'latitude' => null,
            'longitude' => null,
            'order' => 1,
        ]);

        $this->engine->validateQrAndGps($this->team, $stage, $stage->qr_code_uuid);

        $result = $this->engine->validateAnswer($this->team, $stage, 'errado');

        $this->assertFalse($result['correct']);
        $this->assertFalse($result['fatal']);

        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $stage->id,
            'status' => 'answered_wrong',
            'was_correct' => false,
        ]);
    }

    public function test_first_team_bonus(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'correct_answer' => '42',
            'latitude' => null,
            'longitude' => null,
            'order' => 1,
        ]);

        $team2 = Team::factory()->create(['competition_id' => $this->competition->id, 'status' => 'active']);

        foreach ([$this->team, $team2] as $t) {
            $this->engine->validateQrAndGps($t, $stage, $stage->qr_code_uuid);
        }

        $result1 = $this->engine->validateAnswer($this->team, $stage, '42');
        $result2 = $this->engine->validateAnswer($team2, $stage, '42');

        $this->assertTrue($result1['first_team_bonus']);
        $this->assertEquals(GameEngine::BASE_SCORE + GameEngine::FIRST_TEAM_BONUS, $result1['score_earned']);

        $this->assertFalse($result2['first_team_bonus']);
        $this->assertEquals(GameEngine::BASE_SCORE, $result2['score_earned']);
    }

    public function test_complete_stage_updates_team_progress_and_advances(): void
    {
        $stage1 = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'correct_answer' => '111',
            'latitude' => null,
            'longitude' => null,
            'order' => 1,
        ]);
        $stage2 = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'correct_answer' => '222',
            'latitude' => null,
            'longitude' => null,
            'order' => 2,
        ]);

        $this->engine->validateQrAndGps($this->team, $stage1, $stage1->qr_code_uuid);
        $this->engine->validateAnswer($this->team, $stage1, '111');

        $teamProgress = TeamProgress::where('team_id', $this->team->id)
            ->where('competition_id', $this->competition->id)
            ->first();

        $this->assertEquals($stage2->id, $teamProgress->current_stage_id);
        $this->assertEquals(1, $teamProgress->stages_completed);
        $this->assertEquals(GameEngine::BASE_SCORE + GameEngine::FIRST_TEAM_BONUS, $teamProgress->total_score);
    }

    public function test_complete_last_stage_finishes_competition(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'correct_answer' => 'final',
            'latitude' => null,
            'longitude' => null,
            'order' => 1,
        ]);

        $this->engine->validateQrAndGps($this->team, $stage, $stage->qr_code_uuid);
        $this->engine->validateAnswer($this->team, $stage, 'final');

        $teamProgress = TeamProgress::where('team_id', $this->team->id)
            ->where('competition_id', $this->competition->id)
            ->first();

        $this->assertNull($teamProgress->current_stage_id);
        $this->assertNotNull($teamProgress->completed_at);
    }

    public function test_scan_bonus(): void
    {
        $stage = Stage::factory()->create(['competition_id' => $this->competition->id, 'order' => 1]);
        $bonus = BonusOnus::factory()->bonus()->create(['stage_id' => $stage->id]);

        $result = $this->engine->scanBonusOnus($this->team, $bonus);

        $this->assertTrue($result['success']);
        $this->assertEquals(50, $result['bonus_onus']['points']);
        $this->assertEquals('bonus', $result['bonus_onus']['type']);

        $this->assertDatabaseHas('team_bonus_onus', [
            'team_id' => $this->team->id,
            'bonus_onus_id' => $bonus->id,
        ]);
    }

    public function test_scan_onus(): void
    {
        $stage = Stage::factory()->create(['competition_id' => $this->competition->id, 'order' => 1]);
        $onus = BonusOnus::factory()->onus()->create(['stage_id' => $stage->id]);

        $result = $this->engine->scanBonusOnus($this->team, $onus);

        $this->assertTrue($result['success']);
        $this->assertEquals(-30, $result['bonus_onus']['points']);
        $this->assertEquals('onus', $result['bonus_onus']['type']);
    }

    public function test_scan_bonus_onus_duplicate_rejected(): void
    {
        $stage = Stage::factory()->create(['competition_id' => $this->competition->id, 'order' => 1]);
        $bonus = BonusOnus::factory()->bonus()->create(['stage_id' => $stage->id]);

        $this->engine->scanBonusOnus($this->team, $bonus);
        $result = $this->engine->scanBonusOnus($this->team, $bonus);

        $this->assertFalse($result['success']);
        $this->assertEquals('Este bonus/onus ja foi coletado.', $result['message']);
    }

    public function test_validate_word_guess_correct(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'stage_type' => 'enigma_final',
            'word' => 'HELENA',
            'word_score' => 200,
            'latitude' => null,
            'longitude' => null,
            'order' => 5,
        ]);

        $this->engine->validateQrAndGps($this->team, $stage, $stage->qr_code_uuid);

        $result = $this->engine->validateWordGuess($this->team, $stage, 'HELENA');

        $this->assertTrue($result['correct']);
        $this->assertEquals(200 + GameEngine::FIRST_TEAM_BONUS, $result['score_earned']);

        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $stage->id,
            'status' => 'completed',
            'was_correct' => true,
        ]);
    }

    public function test_validate_word_guess_wrong(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'stage_type' => 'enigma_final',
            'word' => 'HELENA',
            'latitude' => null,
            'longitude' => null,
            'order' => 5,
        ]);

        $this->engine->validateQrAndGps($this->team, $stage, $stage->qr_code_uuid);

        $result = $this->engine->validateWordGuess($this->team, $stage, 'GINCANA');

        $this->assertFalse($result['correct']);
        $this->assertFalse($result['fatal']);
        $this->assertEquals(1, $result['attempts']);
    }

    public function test_validate_word_guess_on_non_final(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'stage_type' => 'charada',
            'order' => 1,
        ]);

        $result = $this->engine->validateWordGuess($this->team, $stage, 'teste');

        $this->assertFalse($result['correct']);
        $this->assertTrue($result['fatal']);
        $this->assertEquals('Esta nao e uma etapa de enigma final.', $result['message']);
    }

    public function test_buy_hint(): void
    {
        $stage = Stage::factory()->create(['competition_id' => $this->competition->id, 'latitude' => null, 'longitude' => null, 'order' => 1]);
        $this->engine->validateQrAndGps($this->team, $stage, $stage->qr_code_uuid);

        $hint = Hint::create([
            'stage_id' => $stage->id,
            'hint_text' => 'Dica teste',
            'price' => 10,
            'order' => 1,
        ]);

        $progress = $this->engine->buyHint($this->team, $stage, $hint);

        $this->assertTrue($progress->hint_used);
    }

    public function test_buy_hint_twice_rejected(): void
    {
        $stage = Stage::factory()->create(['competition_id' => $this->competition->id, 'latitude' => null, 'longitude' => null, 'order' => 1]);
        $this->engine->validateQrAndGps($this->team, $stage, $stage->qr_code_uuid);

        $hint = Hint::create([
            'stage_id' => $stage->id,
            'hint_text' => 'Dica teste',
            'price' => 10,
            'order' => 1,
        ]);

        $this->engine->buyHint($this->team, $stage, $hint);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Esta equipe ja comprou uma dica nesta etapa.');

        $this->engine->buyHint($this->team, $stage, $hint);
    }

    public function test_answer_without_qr_scan_throws(): void
    {
        $stage = Stage::factory()->create([
            'competition_id' => $this->competition->id,
            'correct_answer' => 'resposta',
            'latitude' => null,
            'longitude' => null,
            'order' => 1,
        ]);

        $this->expectException(RuntimeException::class);

        $this->engine->validateAnswer($this->team, $stage, 'resposta');
    }

    public function test_scan_bonus_updates_total_score(): void
    {
        $stage = Stage::factory()->create(['competition_id' => $this->competition->id, 'order' => 1]);
        $bonus = BonusOnus::factory()->bonus()->create(['stage_id' => $stage->id]);

        $this->engine->scanBonusOnus($this->team, $bonus);

        $tp = TeamProgress::where('team_id', $this->team->id)
            ->where('competition_id', $this->competition->id)
            ->first();

        $this->assertEquals(50, $tp->total_score);
    }
}
