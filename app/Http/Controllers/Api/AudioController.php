<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\TeamAudioSent;
use App\Http\Controllers\Controller;
use App\Models\Audio;
use App\Models\Stage;
use App\Models\Team;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AudioController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        $data = $request->validate([
            'audio' => 'required|file|mimetypes:audio/mp4,audio/mpeg,audio/wav,audio/aac,audio/x-wav|max:20480',
            'stage_id' => 'nullable|exists:stages,id',
            'duration_seconds' => 'nullable|integer|min:1|max:600',
            'sent_at' => 'nullable|date',
        ]);

        $path = $data['audio']->store("teams/{$team->id}/audios", 'public');

        $stage = !empty($data['stage_id']) ? Stage::findOrFail($data['stage_id']) : null;

        $audio = Audio::create([
            'team_id' => $team->id,
            'stage_id' => $stage?->id,
            'audio_path' => $path,
            'duration_seconds' => $data['duration_seconds'] ?? 0,
            'sent_at' => isset($data['sent_at']) ? \Carbon\Carbon::parse($data['sent_at']) : now(),
        ]);

        if ($stage) {
            $team->proofProgress()->where('proof_id', $stage->proof_id)->increment('audios_count');
        }

        AuditService::log($team, 'audio_uploaded', $stage, [
            'audio_id' => $audio->id,
            'duration_seconds' => $audio->duration_seconds,
        ]);

        event(new TeamAudioSent($team, $stage, $audio));

        return response()->json([
            'success' => true,
            'audio' => [
                'id' => $audio->id,
                'audio_url' => \Storage::url($audio->audio_path),
                'duration_seconds' => $audio->duration_seconds,
                'sent_at' => $audio->sent_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        $audios = $team->audios()
            ->with('stage')
            ->latest('sent_at')
            ->limit(50)
            ->get()
            ->map(fn (Audio $a) => [
                'id' => $a->id,
                'audio_url' => \Storage::url($a->audio_path),
                'duration_seconds' => $a->duration_seconds,
                'sent_at' => $a->sent_at?->toIso8601String(),
                'stage_name' => $a->stage?->name,
            ]);

        return response()->json(['audios' => $audios]);
    }
}
