<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FinalEnigma;
use App\Models\FinalEnigmaQrCode;
use App\Models\Hint;
use App\Models\Proof;
use App\Models\Stage;
use App\Models\Team;
use App\Models\TeamStageProgress;
use App\Services\DistanceCalculator;
use App\Services\GameEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GameEngineTest extends TestCase
{
    use RefreshDatabase;

    private GameEngine $engine;
    private Team $team;
    private Stage $stage;
    private Proof $proof;
    private Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->engine = new GameEngine(new DistanceCalculator);
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
            'penalty' => 10,
            'order' => 1,
        ]);
    }

    public function test_validate_qr_code_success(): void
    {
        $result = $this->engine->validateQrAndGps(
            $this->team,
            $this->stage,
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            -23.550520,
            -46.633309,
        );

        $this->assertTrue($result['success']);
        $this->assertFalse($result['already_completed']);
        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'active',
        ]);
    }

    public function test_validate_qr_code_invalid(): void
    {
        $result = $this->engine->validateQrAndGps(
            $this->team,
            $this->stage,
            'invalid-uuid',
            -23.550520,
            -46.633309,
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('QRCODE_INVALID', $result['error']);
    }

    public function test_validate_qr_code_out_of_range(): void
    {
        $result = $this->engine->validateQrAndGps(
            $this->team,
            $this->stage,
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            -23.600000,
            -46.600000,
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('GPS_OUT_OF_RANGE', $result['error']);
    }

    public function test_validate_answer_correct(): void
    {
        $this->engine->validateQrAndGps($this->team, $this->stage, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', -23.550520, -46.633309);

        $result = $this->engine->validateAnswer($this->team, $this->stage, '12345');

        $this->assertTrue($result['correct']);
        $this->assertEquals(100, $result['score_earned']);
        $this->assertEquals('9999', $result['secret_number']);
        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'completed',
            'was_correct' => true,
        ]);
    }

    public function test_validate_answer_wrong(): void
    {
        $this->engine->validateQrAndGps($this->team, $this->stage, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', -23.550520, -46.633309);

        $result = $this->engine->validateAnswer($this->team, $this->stage, '54321');

        $this->assertFalse($result['correct']);
        $this->assertFalse($result['fatal']);
        $this->assertEquals(1, $result['attempts']);
        $this->assertDatabaseHas('team_stage_progress', [
            'team_id' => $this->team->id,
            'stage_id' => $this->stage->id,
            'status' => 'answered_wrong',
        ]);
    }

    public function test_validate_answer_penalty(): void
    {
        $this->engine->validateQrAndGps($this->team, $this->stage, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', -23.550520, -46.633309);

        $this->engine->validateAnswer($this->team, $this->stage, '00000'); // 1st wrong
        $this->engine->validateAnswer($this->team, $this->stage, '11111'); // 2nd wrong
        $result = $this->engine->validateAnswer($this->team, $this->stage, '12345'); // 3rd attempt, correct

        $this->assertTrue($result['correct']);
        $this->assertEquals(80, $result['score_earned']); // 100 - (3-1)*10
    }

    public function test_buy_hint(): void
    {
        $this->engine->validateQrAndGps($this->team, $this->stage, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', -23.550520, -46.633309);

        $hint = Hint::factory()->create([
            'stage_id' => $this->stage->id,
            'hint_text' => 'Olhe para o relógio da matriz.',
            'price' => 20,
        ]);

        $progress = $this->engine->buyHint($this->team, $this->stage, $hint);

        $this->assertTrue($progress->hint_used);
        $this->assertDatabaseHas('team_progress', [
            'team_id' => $this->team->id,
            'hints_bought' => 1,
        ]);
    }

    public function test_buy_hint_already_bought(): void
    {
        $this->engine->validateQrAndGps($this->team, $this->stage, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', -23.550520, -46.633309);

        $hint = Hint::factory()->create([
            'stage_id' => $this->stage->id,
            'hint_text' => 'Teste',
            'price' => 10,
        ]);

        $this->engine->buyHint($this->team, $this->stage, $hint);

        $this->expectException(\RuntimeException::class);
        $this->engine->buyHint($this->team, $this->stage, $hint);
    }

    public function test_final_enigma_guess_correct(): void
    {
        $enigma = FinalEnigma::factory()->create([
            'competition_id' => $this->competition->id,
            'word' => 'HELENA',
            'final_score' => 500,
            'max_attempts' => 3,
            'cooldown_minutes' => 5,
        ]);

        FinalEnigmaQrCode::factory()
            ->count(6)
            ->sequence(fn ($seq) => ['order' => $seq->index + 1])
            ->create([
                'final_enigma_id' => $enigma->id,
            ]);

        $result = $this->engine->validateFinalEnigmaGuess($this->team, $enigma, 'HELENA');

        $this->assertTrue($result['correct']);
        $this->assertEquals(500, $result['final_score_awarded']);
        $this->assertDatabaseHas('team_final_enigma_attempts', [
            'team_id' => $this->team->id,
            'correct' => true,
            'guessed_word' => 'HELENA',
        ]);
    }

    public function test_final_enigma_guess_wrong(): void
    {
        $enigma = FinalEnigma::factory()->create([
            'competition_id' => $this->competition->id,
            'word' => 'HELENA',
            'max_attempts' => 3,
            'cooldown_minutes' => 5,
        ]);

        FinalEnigmaQrCode::factory()
            ->count(3)
            ->sequence(fn ($seq) => ['order' => $seq->index + 1])
            ->create([
                'final_enigma_id' => $enigma->id,
            ]);

        $result = $this->engine->validateFinalEnigmaGuess($this->team, $enigma, 'GINCANA');

        $this->assertFalse($result['correct']);
        $this->assertNull($result['final_score_awarded']);
        $this->assertEquals(1, $result['attempt_number']);
    }

    public function test_process_photo(): void
    {
        $this->engine->validateQrAndGps($this->team, $this->stage, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', -23.550520, -46.633309);

        $progress = $this->engine->processPhoto($this->team, $this->stage, 'photos/test.jpg');

        $this->assertEquals('photo_sent', $progress->status);
        $this->assertEquals('photos/test.jpg', $progress->photo_path);
        $this->assertDatabaseHas('team_progress', [
            'team_id' => $this->team->id,
            'photos_count' => 1,
        ]);
    }

    public function test_validate_answer_before_qr_throws(): void
    {
        $this->expectException(\RuntimeException::class);

        $progress = TeamStageProgress::where('team_id', $this->team->id)
            ->where('stage_id', $this->stage->id)
            ->firstOrFail();
        $progress->update(['status' => 'locked']);

        $this->engine->validateAnswer($this->team, $this->stage, '12345');
    }
}
