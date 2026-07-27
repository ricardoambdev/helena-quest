<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\TeamScoreUpdated;
use App\Events\TeamStageUpdated;
use App\Models\BonusOnus;
use App\Models\Hint;
use App\Models\Stage;
use App\Models\Team;
use App\Models\TeamBonusOnus;
use App\Models\TeamStageProgress;
use App\Models\TeamProgress;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GameEngine
{
    public const EARTH_RADIUS_METERS = 6371000.0;

    public const BASE_SCORE = 50;
    public const FIRST_TEAM_BONUS = 30;

    public function __construct(
        private readonly DistanceCalculator $distance,
    ) {}

    public function validateQrAndGps(
        Team $team,
        Stage $stage,
        string $qrUuid,
        ?float $gpsLat = null,
        ?float $gpsLng = null,
    ): array {
        if ($stage->qr_code_uuid !== $qrUuid) {
            AuditService::log($team, 'qr_scan_failed', $stage, [
                'error' => 'QRCODE_INVALID',
                'qr_provided' => substr($qrUuid, 0, 8) . '...',
            ]);
            return ['success' => false, 'error' => 'QRCODE_INVALID', 'message' => 'QR Code nao corresponde a esta etapa.'];
        }

        if (($stage->latitude !== null && $stage->longitude !== null) && ($gpsLat === null || $gpsLng === null)) {
            return ['success' => false, 'error' => 'GPS_REQUIRED', 'message' => 'GPS e obrigatorio para esta etapa.'];
        }

        if ($gpsLat !== null && $gpsLng !== null) {
            $distance = $this->distance->haversine(
                $stage->latitude,
                $stage->longitude,
                $gpsLat,
                $gpsLng,
            );

            if ($distance > $stage->radius) {
                AuditService::log($team, 'gps_out_of_range', $stage, [
                    'distance' => round($distance),
                    'max_radius' => $stage->radius,
                    'gps' => [$gpsLat, $gpsLng],
                ]);
                return [
                    'success' => false,
                    'error' => 'GPS_OUT_OF_RANGE',
                    'message' => "Voce esta a " . round($distance) . "m do local (maximo {$stage->radius}m).",
                    'distance' => $distance,
                ];
            }
        }

        $progress = TeamStageProgress::firstOrNew([
            'team_id' => $team->id,
            'stage_id' => $stage->id,
        ]);

        $alreadyCompleted = $progress->status === 'completed';

        if (!$alreadyCompleted) {
            $progress->fill([
                'status' => 'active',
                'qr_scanned_at' => now(),
                'gps_lat' => $gpsLat,
                'gps_lng' => $gpsLng,
                'started_at' => $progress->started_at ?? now(),
            ])->save();

            $this->ensureTeamProgress($team, $stage);

            AuditService::log($team, 'stage_started', $stage, [
                'stage_name' => $stage->name,
                'stage_type' => $stage->stage_type,
                'distance' => $gpsLat !== null ? round($this->distance->haversine($stage->latitude, $stage->longitude, $gpsLat, $gpsLng)) : null,
            ]);

            event(new TeamStageUpdated($team, $stage, $progress));
        }

        return [
            'success' => true,
            'progress' => $progress->fresh(),
            'already_completed' => $alreadyCompleted,
        ];
    }

    public function processPhoto(Team $team, Stage $stage, string $photoPath): TeamStageProgress
    {
        $progress = TeamStageProgress::where('team_id', $team->id)
            ->where('stage_id', $stage->id)
            ->firstOrFail();

        if ($progress->status === 'locked') {
            throw new RuntimeException('QR Code ainda nao foi lido nesta etapa.');
        }

        if ($progress->status === 'completed') {
            throw new RuntimeException('Etapa ja concluida.');
        }

        $progress->update([
            'status' => 'photo_sent',
            'photo_path' => $photoPath,
            'photo_sent_at' => now(),
        ]);

        $this->ensureTeamProgress($team, $stage);

        AuditService::log($team, 'photo_sent', $stage, [
            'stage_name' => $stage->name,
            'photo_path' => $photoPath,
        ]);

        event(new TeamStageUpdated($team, $stage, $progress));

        return $progress->fresh();
    }

    public function validateAnswer(Team $team, Stage $stage, string $answer): array
    {
        $progress = TeamStageProgress::where('team_id', $team->id)
            ->where('stage_id', $stage->id)
            ->firstOrFail();

        if (!in_array($progress->status, ['active', 'photo_sent', 'answered_wrong'], true)) {
            throw new RuntimeException('Nao e possivel responder esta etapa no estado atual (' . $progress->status . ').');
        }

        $isCorrect = hash_equals($stage->correct_answer, $answer);

        $progress->increment('attempts_count');
        $progress->update([
            'last_answer' => $answer,
            'was_correct' => $isCorrect,
            'status' => $isCorrect ? 'answered_correct' : 'answered_wrong',
        ]);

        if ($isCorrect) {
            AuditService::log($team, 'answer_correct', $stage, [
                'stage_name' => $stage->name,
                'attempts' => $progress->attempts_count,
            ]);

            return $this->completeStage($team, $stage, $progress, $answer);
        }

        AuditService::log($team, 'answer_wrong', $stage, [
            'stage_name' => $stage->name,
            'attempts' => $progress->attempts_count,
        ]);

        event(new TeamStageUpdated($team, $stage, $progress));

        return [
            'correct' => false,
            'fatal' => false,
            'message' => 'Resposta incorreta. Tente novamente.',
            'attempts' => $progress->attempts_count,
        ];
    }

    public function completeStage(Team $team, Stage $stage, TeamStageProgress $progress, string $answerDigits): array
    {
        $now = now();
        $elapsed = $progress->started_at ? max(0, (int) $progress->started_at->diffInSeconds($now)) : 0;

        $firstTeamBonus = 0;
        $completedCount = TeamStageProgress::where('stage_id', $stage->id)
            ->where('status', 'completed')
            ->count();
        if ($completedCount === 0) {
            $firstTeamBonus = self::FIRST_TEAM_BONUS;
        }

        $earned = self::BASE_SCORE + $firstTeamBonus;

        $progress->update([
            'status' => 'completed',
            'completed_at' => $now,
            'score_earned' => $earned,
            'time_spent_seconds' => $elapsed,
        ]);

        $teamProgress = $this->ensureTeamProgress($team, $stage);
        $teamProgress->increment('correct_answers');
        $teamProgress->increment('stages_completed');
        $teamProgress->increment('total_score', $earned);
        $teamProgress->increment('total_time_seconds', $elapsed);

        $nextStage = $this->advanceCurrentStage($teamProgress);

        event(new TeamStageUpdated($team, $stage, $progress));
        event(new TeamScoreUpdated($team, $teamProgress));

        return [
            'correct' => true,
            'message' => 'Resposta correta!',
            'secret_number' => $stage->secret_number,
            'next_stage_hint' => $nextStage?->next_stage_hint ?? $stage->next_stage_hint,
            'score_earned' => $earned,
            'first_team_bonus' => $firstTeamBonus > 0,
            'next_stage' => $nextStage ? [
                'id' => $nextStage->id,
                'name' => $nextStage->name,
                'stage_type' => $nextStage->stage_type,
                'latitude' => $nextStage->latitude,
                'longitude' => $nextStage->longitude,
            ] : null,
        ];
    }

    public function unlockWithPassword(Team $team, Stage $stage, string $password): array
    {
        if ($stage->unlock_password === null) {
            return ['success' => false, 'message' => 'Esta etapa nao possui senha de desbloqueio.'];
        }

        if (!hash_equals($stage->unlock_password, $password)) {
            return ['success' => false, 'message' => 'Senha incorreta.'];
        }

        $progress = TeamStageProgress::firstOrNew([
            'team_id' => $team->id,
            'stage_id' => $stage->id,
        ]);

        if ($progress->status !== 'completed') {
            $progress->fill([
                'status' => 'active',
                'started_at' => $progress->started_at ?? now(),
            ])->save();
        }

        return ['success' => true, 'message' => 'Etapa desbloqueada!', 'progress' => $progress->fresh()];
    }

    public function scanBonusOnus(Team $team, BonusOnus $bonusOnus): array
    {
        $existing = TeamBonusOnus::where('team_id', $team->id)
            ->where('bonus_onus_id', $bonusOnus->id)
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'Este bonus/onus ja foi coletado.'];
        }

        TeamBonusOnus::create([
            'team_id' => $team->id,
            'bonus_onus_id' => $bonusOnus->id,
            'collected_at' => now(),
        ]);

        $scoreChange = $bonusOnus->points;

        $teamProgress = TeamProgress::firstOrCreate(
            ['team_id' => $team->id, 'competition_id' => $team->competition_id],
            ['started_at' => now()],
        );
        $teamProgress->increment('total_score', $scoreChange);

        AuditService::log($team, $scoreChange >= 0 ? 'bonus_collected' : 'onus_collected', $bonusOnus, [
            'name' => $bonusOnus->name,
            'score_change' => $scoreChange,
        ]);

        event(new TeamScoreUpdated($team, $teamProgress));

        return [
            'success' => true,
            'bonus_onus' => [
                'id' => $bonusOnus->id,
                'name' => $bonusOnus->name,
                'type' => $bonusOnus->type,
                'points' => $bonusOnus->points,
                'message' => $bonusOnus->message,
            ],
            'total_score' => $teamProgress->fresh()->total_score,
        ];
    }

    public function buyHint(Team $team, Stage $stage, Hint $hint): TeamStageProgress
    {
        $progress = TeamStageProgress::where('team_id', $team->id)
            ->where('stage_id', $stage->id)
            ->firstOrFail();

        if ($progress->hint_used) {
            throw new RuntimeException('Esta equipe ja comprou uma dica nesta etapa.');
        }

        $progress->update(['hint_used' => true]);

        $teamProgress = $this->ensureTeamProgress($team, $stage);
        $teamProgress->increment('hints_bought');

        AuditService::log($team, 'hint_bought', $hint, [
            'stage_name' => $stage->name,
            'hint_price' => $hint->price,
        ]);

        return $progress->fresh();
    }

    public function validateWordGuess(Team $team, Stage $stage, string $word): array
    {
        if ($stage->stage_type !== 'enigma_final') {
            return ['correct' => false, 'fatal' => true, 'message' => 'Esta nao e uma etapa de enigma final.'];
        }

        $progress = TeamStageProgress::where('team_id', $team->id)
            ->where('stage_id', $stage->id)
            ->firstOrFail();

        if ($progress->status === 'completed') {
            return ['correct' => false, 'fatal' => true, 'message' => 'Enigma ja foi resolvido.'];
        }

        $attemptNumber = ($progress->attempts_count ?? 0) + 1;
        $isCorrect = hash_equals(mb_strtolower($stage->word ?? ''), mb_strtolower(trim($word)));

        $progress->increment('attempts_count');
        $progress->update([
            'last_answer' => $word,
            'was_correct' => $isCorrect,
            'status' => $isCorrect ? 'completed' : 'answered_wrong',
        ]);

        if ($isCorrect) {
            $wordScore = $stage->word_score ?? self::BASE_SCORE;

            $firstTeamBonus = 0;
            $completedCount = TeamStageProgress::where('stage_id', $stage->id)
                ->where('status', 'completed')
                ->count();
            if ($completedCount <= 1) {
                $firstTeamBonus = self::FIRST_TEAM_BONUS;
            }

            $earned = $wordScore + $firstTeamBonus;

            $progress->update([
                'completed_at' => now(),
                'score_earned' => $earned,
                'time_spent_seconds' => $progress->started_at ? max(0, (int) $progress->started_at->diffInSeconds(now())) : 0,
            ]);

            $teamProgress = $this->ensureTeamProgress($team, $stage);
            $teamProgress->increment('correct_answers');
            $teamProgress->increment('stages_completed');
            $teamProgress->increment('total_score', $earned);

            event(new TeamStageUpdated($team, $stage, $progress));
            event(new TeamScoreUpdated($team, $teamProgress));

            return [
                'correct' => true,
                'message' => 'PARABENS! Palavra correta! Gincana finalizada!',
                'score_earned' => $earned,
            ];
        }

        $penalty = $stage->wrong_word_penalty ?? 0;
        if ($penalty > 0) {
            $teamProgress = $this->ensureTeamProgress($team, $stage);
            $teamProgress->increment('total_score', -$penalty);
        }

        return [
            'correct' => false,
            'fatal' => false,
            'message' => 'Palavra incorreta.' . ($penalty > 0 ? " -{$penalty}pts" : ''),
            'attempts' => $progress->attempts_count,
        ];
    }

    private function ensureTeamProgress(Team $team, Stage $stage): TeamProgress
    {
        return DB::transaction(function () use ($team, $stage) {
            $progress = TeamProgress::where('team_id', $team->id)
                ->where('competition_id', $team->competition_id)
                ->lockForUpdate()
                ->first();

            if (!$progress) {
                $progress = TeamProgress::create([
                    'team_id' => $team->id,
                    'competition_id' => $team->competition_id,
                    'current_stage_id' => $stage->id,
                    'started_at' => now(),
                ]);
            } else {
                $progress->update(['current_stage_id' => $stage->id]);
            }

            return $progress;
        });
    }

    private function advanceCurrentStage(TeamProgress $teamProgress): ?Stage
    {
        $currentStage = Stage::find($teamProgress->current_stage_id);
        if (!$currentStage) {
            return null;
        }

        $nextStage = Stage::where('competition_id', $currentStage->competition_id)
            ->where('order', '>', $currentStage->order)
            ->orderBy('order')
            ->first();

        if ($nextStage) {
            $teamProgress->update(['current_stage_id' => $nextStage->id]);
        } else {
            $teamProgress->update([
                'current_stage_id' => null,
                'completed_at' => now(),
            ]);
        }

        return $nextStage;
    }
}
