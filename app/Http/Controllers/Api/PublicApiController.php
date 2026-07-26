<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Team;
use App\Models\TeamProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    public function competition(int $id): JsonResponse
    {
        $competition = Competition::with(['teams', 'proofs'])->findOrFail($id);

        return response()->json([
            'id' => $competition->id,
            'name' => $competition->name,
            'description' => $competition->description,
            'year' => $competition->year,
            'status' => $competition->status,
            'started_at' => $competition->started_at?->toIso8601String(),
            'finished_at' => $competition->finished_at?->toIso8601String(),
            'teams' => $competition->teams->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'color' => $t->color_hex,
                'status' => $t->status,
                'crest_url' => $t->crest_path ? \Storage::url($t->crest_path) : null,
            ]),
            'proofs' => $competition->proofs->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'order' => $p->order,
                'status' => $p->status,
            ]),
        ]);
    }

    public function teamsLocation(Request $request, int $competitionId): JsonResponse
    {
        $teams = Team::with('stageProgress')
            ->where('competition_id', $competitionId)
            ->active()
            ->get();

        return response()->json([
            'locations' => $teams->map(fn ($t) => [
                'team_id' => $t->id,
                'name' => $t->name,
                'color' => $t->color_hex,
                'latitude' => optional($t->stageProgress()->latest('updated_at')->first())->gps_lat,
                'longitude' => optional($t->stageProgress()->latest('updated_at')->first())->gps_lng,
                'updated_at' => optional($t->stageProgress()->latest('updated_at')->first())->updated_at?->toIso8601String(),
            ]),
        ]);
    }

    public function ranking(Request $request, int $competitionId): JsonResponse
    {
        $rows = \DB::table('team_progress')
            ->join('teams', 'teams.id', '=', 'team_progress.team_id')
            ->where('teams.competition_id', $competitionId)
            ->whereNull('teams.deleted_at')
            ->select(
                'teams.id as team_id',
                'teams.name',
                'teams.color_hex',
                \DB::raw('SUM(team_progress.total_score) as total_score'),
                \DB::raw('SUM(team_progress.total_time_seconds) as total_time'),
                \DB::raw('SUM(team_progress.stages_completed) as stages_completed'),
                \DB::raw('SUM(team_progress.correct_answers) as correct'),
                \DB::raw('SUM(team_progress.wrong_answers) as wrong'),
                \DB::raw('SUM(team_progress.hints_bought) as hints'),
            )
            ->groupBy('teams.id', 'teams.name', 'teams.color_hex')
            ->orderByDesc('total_score')
            ->orderBy('total_time')
            ->get();

        return response()->json([
            'ranking' => $rows->map(fn ($r, $i) => [
                'position' => $i + 1,
                'team_id' => $r->team_id,
                'name' => $r->name,
                'color' => $r->color_hex,
                'total_score' => (int) $r->total_score,
                'total_time' => (int) $r->total_time,
                'stages_completed' => (int) $r->stages_completed,
                'correct' => (int) $r->correct,
                'wrong' => (int) $r->wrong,
                'hints' => (int) $r->hints,
            ]),
        ]);
    }

    public function progress(Request $request, int $competitionId): JsonResponse
    {
        $total = \DB::table('stages')
            ->join('proofs', 'proofs.id', '=', 'stages.proof_id')
            ->where('proofs.competition_id', $competitionId)
            ->count();

        $teams = Team::where('competition_id', $competitionId)->get();

        $rows = $teams->map(function (Team $t) use ($total) {
            $done = $t->stageProgress()->where('status', 'completed')->count();
            $percent = $total > 0 ? round(($done / $total) * 100) : 0;

            return [
                'team_id' => $t->id,
                'name' => $t->name,
                'color' => $t->color_hex,
                'stages_completed' => $done,
                'stages_total' => $total,
                'percent' => $percent,
            ];
        });

        return response()->json(['progress' => $rows]);
    }

    public function photos(int $competitionId): JsonResponse
    {
        $photos = \DB::table('team_stage_progress')
            ->join('teams', 'teams.id', '=', 'team_stage_progress.team_id')
            ->where('teams.competition_id', $competitionId)
            ->whereNotNull('team_stage_progress.photo_path')
            ->orderByDesc('team_stage_progress.photo_sent_at')
            ->limit(40)
            ->select('team_stage_progress.photo_path', 'team_stage_progress.photo_sent_at', 'teams.name as team_name', 'teams.color_hex as team_color')
            ->get()
            ->map(fn ($r) => [
                'photo_url' => \Storage::url($r->photo_path),
                'sent_at' => $r->photo_sent_at,
                'team_name' => $r->team_name,
                'team_color' => $r->team_color,
            ]);

        return response()->json(['photos' => $photos]);
    }

    public function audios(int $competitionId): JsonResponse
    {
        $audios = \DB::table('audios')
            ->join('teams', 'teams.id', '=', 'audios.team_id')
            ->where('teams.competition_id', $competitionId)
            ->orderByDesc('audios.sent_at')
            ->limit(20)
            ->select('audios.audio_path', 'audios.duration_seconds', 'audios.sent_at', 'teams.name as team_name', 'teams.color_hex as team_color')
            ->get()
            ->map(fn ($r) => [
                'audio_url' => \Storage::url($r->audio_path),
                'duration_seconds' => $r->duration_seconds,
                'sent_at' => $r->sent_at,
                'team_name' => $r->team_name,
                'team_color' => $r->team_color,
            ]);

        return response()->json(['audios' => $audios]);
    }
}
