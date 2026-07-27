<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\Team;
use App\Services\AuditService;
use App\Services\GameEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinalEnigmaController extends Controller
{
    public function __construct(private readonly GameEngine $engine) {}

    public function status(Request $request): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        $stage = Stage::where('competition_id', $team->competition_id)
            ->where('stage_type', 'enigma_final')
            ->first();

        if (!$stage) {
            return response()->json(['enabled' => false]);
        }

        $progress = $team->stageProgress()
            ->where('stage_id', $stage->id)
            ->first();

        $scannedCofres = $progress?->bonus_onus_ids ?? [];

        return response()->json([
            'enabled' => true,
            'stage_id' => $stage->id,
            'stage_name' => $stage->name,
            'max_attempts' => $stage->max_attempts ?? 5,
            'attempts_made' => $progress?->attempts_count ?? 0,
            'correct_attempts' => $progress?->was_correct ? 1 : 0,
            'locked' => false,
            'cofres_unlocked' => count($scannedCofres),
            'required_cofres' => $stage->sub_questions !== null ? count($stage->sub_questions) : 1,
            'word' => $progress?->status === 'completed' ? $stage->word : null,
        ]);
    }

    public function validateCofre(Request $request): JsonResponse
    {
        $data = $request->validate([
            'uuid' => 'required|string|size:36',
        ]);

        /** @var Team $team */
        $team = $request->user();

        $stage = Stage::where('competition_id', $team->competition_id)
            ->where('stage_type', 'enigma_final')
            ->firstOrFail();

        $bonus = \App\Models\BonusOnus::where('stage_id', $stage->id)
            ->where('qr_code_uuid', $data['uuid'])
            ->first();

        if (!$bonus) {
            return response()->json(['success' => false, 'message' => 'Cofre nao encontrado.'], 404);
        }

        $result = $this->engine->scanBonusOnus($team, $bonus);

        AuditService::log($team, 'enigma_cofre_scanned', $stage, [
            'bonus_name' => $bonus->name,
            'points' => $bonus->points,
        ]);

        return response()->json($result);
    }

    public function guess(Request $request): JsonResponse
    {
        $data = $request->validate([
            'word' => 'required|string|max:50',
        ]);

        /** @var Team $team */
        $team = $request->user();

        $stage = Stage::where('competition_id', $team->competition_id)
            ->where('stage_type', 'enigma_final')
            ->firstOrFail();

        $result = $this->engine->validateWordGuess($team, $stage, $data['word']);

        return response()->json($result);
    }

    public function attempts(Request $request): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        $stage = Stage::where('competition_id', $team->competition_id)
            ->where('stage_type', 'enigma_final')
            ->firstOrFail();

        $progress = $team->stageProgress()
            ->where('stage_id', $stage->id)
            ->first();

        if (!$progress) {
            return response()->json(['attempts' => []]);
        }

        return response()->json([
            'attempts' => [
                [
                    'attempt_number' => $progress->attempts_count,
                    'guessed_word' => $progress->last_answer,
                    'correct' => (bool) $progress->was_correct,
                    'created_at' => $progress->updated_at?->toIso8601String(),
                ],
            ],
        ]);
    }
}
