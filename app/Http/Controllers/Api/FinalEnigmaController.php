<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinalEnigma;
use App\Models\FinalEnigmaQrCode;
use App\Models\Team;
use App\Models\TeamFinalEnigmaLetter;
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

        $enigma = FinalEnigma::where('competition_id', $team->competition_id)->first();

        if (!$enigma) {
            return response()->json(['enabled' => false]);
        }

        $attempts = $team->finalEnigmaAttempts()
            ->where('final_enigma_id', $enigma->id)
            ->orderByDesc('attempt_number')
            ->get();

        $lastFailed = $attempts->where('correct', false)->first();

        // Letters unlocked by scanned QRs
        $scannedLetters = TeamFinalEnigmaLetter::where('team_id', $team->id)
            ->where('final_enigma_id', $enigma->id)
            ->pluck('letter')
            ->all();

        return response()->json([
            'enabled' => true,
            'max_attempts' => $enigma->max_attempts,
            'attempts_made' => $attempts->count(),
            'correct_attempts' => $attempts->where('correct', true)->count(),
            'next_available_at' => $lastFailed?->next_available_at?->toIso8601String(),
            'locked' => $lastFailed && $lastFailed->next_available_at && now()->lt($lastFailed->next_available_at),
            'letters_unlocked' => $scannedLetters,
            'required_letters_count' => $enigma->qrCodes->count(),
        ]);
    }

    public function validateLetter(Request $request, string $qr): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        $code = FinalEnigmaQrCode::with('finalEnigma')
            ->where('qr_code_uuid', $qr)
            ->firstOrFail();

        if ($code->finalEnigma->competition_id !== $team->competition_id) {
            return response()->json(['success' => false, 'message' => 'QR Code não pertence à sua competição.'], 403);
        }

        TeamFinalEnigmaLetter::insertOrIgnore([
            'team_id' => $team->id,
            'final_enigma_id' => $code->final_enigma_id,
            'final_enigma_qr_code_id' => $code->id,
            'letter' => $code->letter,
            'scanned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AuditService::log($team, 'final_letter_scanned', $code->finalEnigma, [
            'letter' => $code->letter,
        ]);

        return response()->json([
            'success' => true,
            'letter' => $code->letter,
            'hint_text' => $code->hint_text,
        ]);
    }

    public function guess(Request $request): JsonResponse
    {
        $data = $request->validate([
            'word' => 'required|string|max:50',
        ]);

        /** @var Team $team */
        $team = $request->user();

        $enigma = FinalEnigma::where('competition_id', $team->competition_id)->firstOrFail();

        $result = $this->engine->validateFinalEnigmaGuess($team, $enigma, $data['word']);

        return response()->json($result);
    }

    public function attempts(Request $request): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        $attempts = $team->finalEnigmaAttempts()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($a) => [
                'attempt_number' => $a->attempt_number,
                'guessed_word' => $a->guessed_word,
                'correct' => (bool) $a->correct,
                'cooldown_ends_at' => $a->next_available_at?->toIso8601String(),
                'created_at' => $a->created_at?->toIso8601String(),
            ]);

        return response()->json(['attempts' => $attempts]);
    }
}
