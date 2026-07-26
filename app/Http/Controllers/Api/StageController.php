<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\TeamLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\Team;
use App\Services\GameEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class StageController extends Controller
{
    public function __construct(private readonly GameEngine $engine) {}

    public function current(Request $request): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        $progress = $team->proofProgress()->whereNotNull('current_stage_id')->first();

        if (!$progress || !$progress->current_stage_id) {
            return response()->json([
                'has_stage' => false,
                'message' => 'Aguarde a organização iniciar a prova.',
            ]);
        }

        $stage = Stage::with(['proof', 'hints'])->findOrFail($progress->current_stage_id);

        $pivot = $team->stageProgress()->where('stage_id', $stage->id)->first();

        return response()->json([
            'has_stage' => true,
            'stage' => [
                'id' => $stage->id,
                'name' => $stage->name,
                'description' => $stage->description,
                'latitude' => $stage->latitude,
                'longitude' => $stage->longitude,
                'radius' => $stage->radius,
                'narrative_text' => $stage->narrative_text,
                'image_url' => $stage->image_path ? \Storage::url($stage->image_path) : null,
                'hints' => $stage->hints->map(fn ($h) => [
                    'id' => $h->id,
                    'price' => $h->price,
                    'already_bought' => (bool) $pivot?->hint_used,
                ]),
                'accepts_photo' => in_array($pivot?->status, ['active'], true),
                'photo_sent' => $pivot?->status === 'photo_sent' || $pivot?->status === 'answered_wrong' || $pivot?->status === 'answered_correct',
                'accepts_answer' => in_array($pivot?->status, ['active', 'photo_sent', 'answered_wrong'], true),
                'correct_answer_length' => strlen($stage->correct_answer),
            ],
            'progress_status' => $pivot?->status,
            'attempts_count' => $pivot?->attempts_count ?? 0,
        ]);
    }

    public function validateQr(Request $request, Stage $stage): JsonResponse
    {
        $data = $request->validate([
            'qr_code_uuid' => 'required|string|size:36',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        /** @var Team $team */
        $team = $request->user();

        try {
            $result = $this->engine->validateQrAndGps(
                $team,
                $stage,
                $data['qr_code_uuid'],
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
            );

            if (!empty($data['latitude'])) {
                event(new TeamLocationUpdated($team, $data['latitude'], $data['longitude']));
            }

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendPhoto(Request $request, Stage $stage): JsonResponse
    {
        $data = $request->validate([
            'photo' => 'required|image|max:10240',
        ]);

        /** @var Team $team */
        $team = $request->user();

        $path = $data['photo']->store("teams/{$team->id}/photos", 'public');

        try {
            $progress = $this->engine->processPhoto($team, $stage, $path);

            event(new \App\Events\TeamPhotoSent($team, $stage, $path));

            return response()->json([
                'success' => true,
                'photo_url' => \Storage::url($path),
                'status' => $progress->status,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function answer(Request $request, Stage $stage): JsonResponse
    {
        $data = $request->validate([
            'answer' => 'required|string|max:20',
        ]);

        /** @var Team $team */
        $team = $request->user();

        try {
            $result = $this->engine->validateAnswer($team, $stage, $data['answer']);

            $status = ($result['correct'] ?? false) ? 200 : (($result['fatal'] ?? false) ? 422 : 200);

            return response()->json($result, $status);
        } catch (Throwable $e) {
            return response()->json(['correct' => false, 'fatal' => true, 'message' => $e->getMessage()], 422);
        }
    }

    public function hints(Request $request, Stage $stage): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        $pivot = $team->stageProgress()->where('stage_id', $stage->id)->first();
        $hintBought = $pivot?->hint_used ?? false;

        $hints = $stage->hints()->orderBy('order')->get();

        return response()->json([
            'hints' => $hints->map(fn ($h) => [
                'id' => $h->id,
                'price' => $h->price,
                'text' => $hintBought ? $h->hint_text : null,
                'locked' => !$hintBought,
            ]),
        ]);
    }

    public function buyHint(Request $request, Stage $stage, \App\Models\Hint $hint): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        try {
            $progress = $this->engine->buyHint($team, $stage, $hint);

            return response()->json([
                'success' => true,
                'hint_text' => $hint->hint_text,
                'price' => $hint->price,
                'state' => $progress->status,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
