<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Audio;
use App\Models\Competition;
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
        return Competition::with(['teams', 'stages'])->findOrFail($this->competitionId);
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
        $currentStageMap = $this->teamCurrentStage();

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
                'current_stage' => $currentStageMap[$id] ?? '—',
            ])
            ->sortByDesc('total_score')
            ->values()
            ->toArray();
    }

    #[Computed]
    public function teamCurrentStage(): array
    {
        $teamIds = $this->activeTeamIds;
        if (empty($teamIds)) {
            return [];
        }

        $stages = $this->competition->stages->keyBy('id');

        $progress = TeamProgress::whereIn('team_id', $teamIds)
            ->whereNotNull('current_stage_id')
            ->get()
            ->keyBy('team_id');

        $map = [];
        foreach ($teamIds as $id) {
            $tp = $progress->get($id);
            $stageId = $tp?->current_stage_id;
            $map[$id] = $stageId && $stages->has($stageId)
                ? $stages[$stageId]->name
                : ($tp && !$tp->current_stage_id ? 'Finalizou' : '—');
        }
        return $map;
    }

    #[Computed]
    public function schoolLocation(): ?array
    {
        $comp = $this->competition;
        if ($comp->school_latitude && $comp->school_longitude) {
            return [
                'lat' => (float) $comp->school_latitude,
                'lng' => (float) $comp->school_longitude,
                'name' => $comp->school_name ?? 'Escola',
                'logo' => $comp->school_logo_path ? \Storage::url($comp->school_logo_path) : null,
            ];
        }
        return null;
    }

    #[Computed]
    public function progress(): array
    {
        return [];
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
                'crest_url' => $teamMap->get($id)?->crest_path ? \Storage::url($teamMap->get($id)->crest_path) : null,
                'lat' => $latest->get($id)?->gps_lat,
                'lng' => $latest->get($id)?->gps_lng,
                'updated_at' => $latest->get($id)?->updated_at?->toIso8601String(),
            ])
            ->values()
            ->toArray();
    }

    public function render(): mixed
    {
        return view('livewire.telao');
    }
}
