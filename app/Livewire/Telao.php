<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Audio;
use App\Models\Competition;
use App\Models\FinalEnigma;
use App\Models\TeamFinalEnigmaAttempt;
use App\Models\TeamFinalEnigmaLetter;
use App\Models\TeamProgress;
use App\Models\TeamStageProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Telao extends Component
{
    public int $competitionId;

    public function mount(int $competition): void
    {
        $this->competitionId = $competition;
    }

    #[Computed]
    public function competition(): Competition
    {
        return Competition::with(['teams', 'proofs.stages'])->findOrFail($this->competitionId);
    }

    #[Computed]
    public function activeTeamIds(): array
    {
        return $this->competition->teams
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();
    }

    #[Computed]
    public function ranking(): array
    {
        $teamIds = $this->activeTeamIds;
        if (empty($teamIds)) {
            return [];
        }

        $teamMap = $this->competition->teams->where('status', 'active')->keyBy('id');

        $ranking = TeamProgress::whereIn('team_id', $teamIds)
            ->selectRaw('team_id, SUM(total_score) as total_score, SUM(total_time_seconds) as total_time, SUM(stages_completed) as total_stages, SUM(correct_answers) as correct_answers, SUM(photos_count) as photos_count, SUM(audios_count) as audios_count')
            ->groupBy('team_id')
            ->get()
            ->keyBy('team_id');

        return collect($teamIds)
            ->map(fn (int $id) => [
                'id' => $id,
                'name' => $teamMap->get($id)?->name ?? '?',
                'color_hex' => $teamMap->get($id)?->color_hex ?? '#CCCCCC',
                'crest_url' => $teamMap->get($id)?->crest_path ? \Storage::url($teamMap->get($id)->crest_path) : null,
                'total_score' => (int) ($ranking->get($id)?->total_score ?? 0),
                'total_time' => (int) ($ranking->get($id)?->total_time ?? 0),
                'total_stages' => (int) ($ranking->get($id)?->total_stages ?? 0),
                'correct_answers' => (int) ($ranking->get($id)?->correct_answers ?? 0),
                'photos_count' => (int) ($ranking->get($id)?->photos_count ?? 0),
                'audios_count' => (int) ($ranking->get($id)?->audios_count ?? 0),
            ])
            ->sortByDesc('total_score')
            ->values()
            ->toArray();
    }

    #[Computed]
    public function progress(): array
    {
        $teamIds = $this->activeTeamIds;
        if (empty($teamIds)) {
            return [];
        }

        $allStagesProgress = TeamStageProgress::whereIn('team_id', $teamIds)
            ->get()
            ->groupBy('stage_id');

        return $this->competition->proofs->map(fn ($proof) => [
            'id' => $proof->id,
            'name' => $proof->name,
            'order' => $proof->order,
            'color_hex' => $proof->color_hex,
            'stages' => $proof->stages->map(fn ($stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'order' => $stage->order,
                'completed_count' => $allStagesProgress->get($stage->id)?->where('status', 'completed')->count() ?? 0,
                'active_count' => $allStagesProgress->get($stage->id)?->filter(fn ($p) => in_array($p->status, ['active', 'photo_sent', 'answered_wrong'], true))->count() ?? 0,
                'total' => count($teamIds),
            ])->values()->toArray(),
        ])->sortBy('order')->values()->toArray();
    }

    #[Computed]
    public function recentPhotos(): array
    {
        $teamIds = $this->activeTeamIds;
        if (empty($teamIds)) {
            return [];
        }

        $teamMap = $this->competition->teams->where('status', 'active')->keyBy('id');

        return TeamStageProgress::whereIn('team_id', $teamIds)
            ->whereNotNull('photo_path')
            ->whereNotNull('photo_sent_at')
            ->latest('photo_sent_at')
            ->limit(30)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'team_id' => $p->team_id,
                'team_name' => $teamMap->get($p->team_id)?->name ?? '?',
                'team_color' => $teamMap->get($p->team_id)?->color_hex ?? '#CCCCCC',
                'photo_url' => \Storage::url($p->photo_path),
                'sent_at' => $p->photo_sent_at?->toIso8601String(),
            ])
            ->toArray();
    }

    #[Computed]
    public function recentAudios(): array
    {
        $teamIds = $this->activeTeamIds;
        if (empty($teamIds)) {
            return [];
        }

        $teamMap = $this->competition->teams->where('status', 'active')->keyBy('id');

        return Audio::whereIn('team_id', $teamIds)
            ->with('stage')
            ->latest('sent_at')
            ->limit(20)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'team_id' => $a->team_id,
                'team_name' => $teamMap->get($a->team_id)?->name ?? '?',
                'team_color' => $teamMap->get($a->team_id)?->color_hex ?? '#CCCCCC',
                'stage_name' => $a->stage?->name,
                'audio_url' => \Storage::url($a->audio_path),
                'duration_seconds' => $a->duration_seconds,
                'sent_at' => $a->sent_at?->toIso8601String(),
            ])
            ->toArray();
    }

    #[Computed]
    public function teamLocations(): array
    {
        $teamIds = $this->activeTeamIds;
        if (empty($teamIds)) {
            return [];
        }

        $teamMap = $this->competition->teams->where('status', 'active')->keyBy('id');

        $latest = TeamStageProgress::whereIn('team_id', $teamIds)
            ->whereNotNull('gps_lat')
            ->whereNotNull('gps_lng')
            ->latest('updated_at')
            ->get()
            ->groupBy('team_id')
            ->map->first();

        return collect($teamIds)
            ->map(fn (int $id) => [
                'team_id' => $id,
                'team_name' => $teamMap->get($id)?->name ?? '?',
                'team_color' => $teamMap->get($id)?->color_hex ?? '#CCCCCC',
                'lat' => $latest->get($id)?->gps_lat,
                'lng' => $latest->get($id)?->gps_lng,
                'updated_at' => $latest->get($id)?->updated_at?->toIso8601String(),
            ])
            ->values()
            ->toArray();
    }

    #[Computed]
    public function finalEnigmaStatus(): ?array
    {
        $teamIds = $this->activeTeamIds;
        if (empty($teamIds)) {
            return null;
        }

        $enigma = FinalEnigma::where('competition_id', $this->competitionId)->first();
        if (!$enigma) {
            return null;
        }

        $solvedTeamIds = TeamFinalEnigmaAttempt::where('final_enigma_id', $enigma->id)
            ->where('correct', true)
            ->pluck('team_id')
            ->all();

        $letterCounts = TeamFinalEnigmaLetter::where('final_enigma_id', $enigma->id)
            ->whereIn('team_id', $teamIds)
            ->selectRaw('team_id, COUNT(*) as count')
            ->groupBy('team_id')
            ->get()
            ->keyBy('team_id');

        $teamMap = $this->competition->teams->where('status', 'active')->keyBy('id');
        $requiredLetters = $enigma->qrCodes->count();

        $statuses = [];
        foreach ($teamIds as $id) {
            $statuses[] = [
                'team_id' => $id,
                'team_name' => $teamMap->get($id)?->name ?? '?',
                'team_color' => $teamMap->get($id)?->color_hex ?? '#CCCCCC',
                'solved' => in_array($id, $solvedTeamIds, true),
                'letters_collected' => (int) ($letterCounts->get($id)?->count ?? 0),
                'required_letters' => $requiredLetters,
                'word' => $enigma->word,
            ];
        }

        return [
            'enabled' => true,
            'required_letters' => $requiredLetters,
            'teams' => $statuses,
        ];
    }

    public function render(): mixed
    {
        return view('livewire.telao');
    }
}
