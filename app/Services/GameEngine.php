<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\TeamScoreUpdated;
use App\Events\TeamStageUpdated;
use App\Models\FinalEnigma;
use App\Models\Hint;
use App\Models\Proof;
use App\Models\Stage;
use App\Models\Team;
use App\Models\TeamFinalEnigmaAttempt;
use App\Models\TeamProgress;
use App\Models\TeamStageProgress;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GameEngine
{
    public const EARTH_RADIUS_METERS = 6371000.0;

    public function __construct(
        private readonly DistanceCalculator $distance,
    ) {}

    /**
     * Valida QR Code + GPS: cria/atualiza TeamStageProgress, retorna sucesso.
     */
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
            return ['success' => false, 'error' => 'QRCODE_INVALID', 'message' => 'QR Code não corresponde a esta etapa.'];
        }

        if (($stage->latitude !== null && $stage->longitude !== null) && ($gpsLat === null || $gpsLng === null)) {
            return ['success' => false, 'error' => 'GPS_REQUIRED', 'message' => 'GPS é obrigatório para esta etapa.'];
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
                    'message' => "Você está a " . round($distance) . "m do local (máximo {$stage->radius}m).",
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
                'proof_id' => $stage->proof_id,
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

    /**
     * Processa o envio de foto: ela libera a resposta.
     */
    public function processPhoto(Team $team, Stage $stage, string $photoPath): TeamStageProgress
    {
        $progress = TeamStageProgress::where('team_id', $team->id)
            ->where('stage_id', $stage->id)
            ->firstOrFail();

        if ($progress->status === 'locked') {
            throw new RuntimeException('QR Code ainda não foi lido nesta etapa.');
        }

        if ($progress->status === 'completed') {
            throw new RuntimeException('Etapa já concluída.');
        }

        $progress->update([
            'status' => 'photo_sent',
            'photo_path' => $photoPath,
            'photo_sent_at' => now(),
        ]);

        $teamProgress = $this->ensureTeamProgress($team, $stage);
        $teamProgress->increment('photos_count');

        AuditService::log($team, 'photo_sent', $stage, [
            'stage_name' => $stage->name,
            'photo_path' => $photoPath,
        ]);

        event(new TeamStageUpdated($team, $stage, $progress));

        return $progress->fresh();
    }

    /**
     * Valida a resposta (formato + correção) e, se correta, completa a etapa.
     */
    public function validateAnswer(Team $team, Stage $stage, string $answer): array
    {
        $digits = preg_replace('/\D+/', '', $answer) ?? '';

        if (strlen($digits) < 4 || strlen($digits) > 8) {
            return [
                'correct' => false,
                'fatal' => true,
                'message' => 'A resposta deve ter entre 4 e 8 dígitos numéricos.',
            ];
        }

        if (!is_numeric($digits)) {
            return [
                'correct' => false,
                'fatal' => true,
                'message' => 'A resposta deve conter apenas números.',
            ];
        }

        $progress = TeamStageProgress::where('team_id', $team->id)
            ->where('stage_id', $stage->id)
            ->firstOrFail();

        if (!in_array($progress->status, ['active', 'photo_sent', 'answered_wrong'], true)) {
            throw new RuntimeException('Não é possível responder esta etapa no estado atual (' . $progress->status . ').');
        }

        $isCorrect = hash_equals($stage->correct_answer, $digits);

        $progress->increment('attempts_count');
        $progress->update([
            'last_answer' => $digits,
            'was_correct' => $isCorrect,
            'status' => $isCorrect ? 'answered_correct' : 'answered_wrong',
        ]);

        if ($isCorrect) {
            AuditService::log($team, 'answer_correct', $stage, [
                'stage_name' => $stage->name,
                'attempts' => $progress->attempts_count,
            ]);
            return $this->completeStage($team, $stage, $progress, $digits);
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

    /**
     * Conclui a etapa: registra tempo, número secreto, libera próxima dica, atualiza pontuação.
     */
    public function completeStage(Team $team, Stage $stage, TeamStageProgress $progress, string $answerDigits): array
    {
        $now = now();
        $elapsed = $progress->started_at ? max(0, (int) $progress->started_at->diffInSeconds($now)) : 0;
        $earned = max(0, ($stage->score ?? 100) - ($progress->attempts_count - 1) * ($stage->penalty ?? 0));

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
            'next_stage' => $nextStage ? [
                'id' => $nextStage->id,
                'name' => $nextStage->name,
                'latitude' => $nextStage->latitude,
                'longitude' => $nextStage->longitude,
            ] : null,
        ];
    }

    /**
     * Registra a compra de uma dica (RF-063).
     */
    public function buyHint(Team $team, Stage $stage, \App\Models\Hint $hint): TeamStageProgress
    {
        $progress = TeamStageProgress::where('team_id', $team->id)
            ->where('stage_id', $stage->id)
            ->firstOrFail();

        if ($progress->hint_used) {
            throw new RuntimeException('Esta equipe já comprou uma dica nesta etapa.');
        }

        $progress->update([
            'hint_used' => true,
        ]);

        $teamProgress = $this->ensureTeamProgress($team, $stage);
        $teamProgress->increment('hints_bought');

        AuditService::log($team, 'hint_bought', $hint, [
            'stage_name' => $stage->name,
            'hint_price' => $hint->price,
        ]);

        return $progress->fresh();
    }

    /**
     * Calcula a chave do enigma final: concatenação reversa dos números secretos
     * de TODAS as etapas concluídas pela equipe na competição.
     */
    public function calculateChaveFinal(Team $team, ?int $competitionId = null): string
    {
        $compId = $competitionId ?? $team->competition_id;

        return $team->stageProgress()
            ->whereHas('stage.proof', fn ($q) => $q->where('competition_id', $compId))
            ->where('status', 'completed')
            ->with('stage')
            ->get()
            ->sortByDesc(fn ($p) => $p->stage->order)
            ->pluck('stage.secret_number')
            ->implode('');
    }

    /**
     * Valida tentativa de palavra do enigma final, controla cooldown.
     */
    public function validateFinalEnigmaGuess(Team $team, \App\Models\FinalEnigma $enigma, string $word): array
    {
        $lastAttempt = TeamFinalEnigmaAttempt::where('team_id', $team->id)
            ->where('final_enigma_id', $enigma->id)
            ->orderByDesc('attempt_number')
            ->first();

        if ($lastAttempt && !$lastAttempt->correct && $lastAttempt->next_available_at) {
            if (now()->lt($lastAttempt->next_available_at)) {
                return [
                    'correct' => false,
                    'locked' => true,
                    'message' => "Aguarde o cooldown até " . $lastAttempt->next_available_at->format('H:i:s'),
                    'next_available_at' => $lastAttempt->next_available_at,
                ];
            }
        }

        $attemptNumber = ($lastAttempt?->attempt_number ?? 0) + 1;

        $normalized = mb_strtolower(trim($word));
        $expected = mb_strtolower($enigma->word);

        $isCorrect = hash_equals($expected, $normalized);

        $attempt = TeamFinalEnigmaAttempt::create([
            'team_id' => $team->id,
            'final_enigma_id' => $enigma->id,
            'attempt_number' => $attemptNumber,
            'guessed_word' => $word,
            'correct' => $isCorrect,
            'next_available_at' => match (true) {
                $isCorrect => null,
                $attemptNumber >= $enigma->max_attempts => now()->addMinutes($enigma->cooldown_minutes),
                default => null,
            },
        ]);

        AuditService::log($team, $isCorrect ? 'final_enigma_solved' : 'final_enigma_guess', $enigma, [
            'word' => $word,
            'attempt_number' => $attemptNumber,
            'max_attempts' => $enigma->max_attempts,
        ]);

        $finalScore = $isCorrect ? ($enigma->final_score ?? 500) : 0;

        $awarded = null;
        if ($finalScore > 0) {
            $firstProof = Proof::where('competition_id', $enigma->competition_id)
                ->orderBy('order')
                ->first();

            if ($firstProof) {
                $teamProgress = TeamProgress::firstOrCreate(
                    ['team_id' => $team->id, 'proof_id' => $firstProof->id],
                    ['started_at' => now()],
                );

                $teamProgress->increment('total_score', $finalScore);
                $teamProgress->increment('stages_completed');
                $teamProgress->increment('correct_answers');

                event(new TeamScoreUpdated($team, $teamProgress));
                $awarded = $finalScore;
            }
        }

        return [
            'correct' => $isCorrect,
            'locked' => false,
            'attempt_number' => $attemptNumber,
            'final_score_awarded' => $awarded,
            'message' => $isCorrect
                ? "PARABÉNS! Palavra correta. Gincana finalizada! (+{$finalScore}pts)"
                : "Tentativa $attemptNumber de {$enigma->max_attempts} registrada.",
        ];
    }

    private function ensureTeamProgress(Team $team, Stage $stage): TeamProgress
    {
        return DB::transaction(function () use ($team, $stage) {
            $progress = TeamProgress::where('team_id', $team->id)
                ->where('proof_id', $stage->proof_id)
                ->lockForUpdate()
                ->first();

            if (!$progress) {
                $progress = TeamProgress::create([
                    'team_id' => $team->id,
                    'proof_id' => $stage->proof_id,
                    'current_stage_id' => $stage->id,
                    'started_at' => now(),
                ]);
            } elseif (!$progress->current_stage_id || $progress->current_stage_id === $stage->id) {
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

        $nextStage = Stage::where('proof_id', $currentStage->proof_id)
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
