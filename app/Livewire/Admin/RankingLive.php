<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\TeamProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Ranking ao vivo')]
class RankingLive extends Component
{
    public ?int $competitionId = null;

    #[Computed]
    public function competitionsList()
    {
        return Competition::orderByDesc('year')->get();
    }

    #[Computed]
    public function ranking()
    {
        if (!$this->competitionId) {
            return collect();
        }

        return TeamProgress::query()
            ->join('teams', 'teams.id', '=', 'team_progress.team_id')
            ->where('teams.competition_id', $this->competitionId)
            ->whereNull('teams.deleted_at')
            ->groupBy('teams.id', 'teams.name', 'teams.color_hex')
            ->selectRaw('
                teams.id, teams.name, teams.color_hex,
                COALESCE(SUM(team_progress.total_score),0) as total_score,
                COALESCE(SUM(team_progress.total_time_seconds),0) as total_time,
                COALESCE(SUM(team_progress.stages_completed),0) as stages_completed,
                COALESCE(SUM(team_progress.correct_answers),0) as correct,
                COALESCE(SUM(team_progress.wrong_answers),0) as wrong,
                COALESCE(SUM(team_progress.hints_bought),0) as hints
            ')
            ->orderByDesc('total_score')
            ->orderBy('total_time')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.ranking-live');
    }
}
