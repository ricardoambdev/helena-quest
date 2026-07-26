<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Proof;
use App\Models\Team;
use App\Models\TeamProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Painel geral — Helena Quest')]
class Dashboard extends Component
{
    #[Computed]
    public function totals(): array
    {
        return [
            'competitions' => Competition::count(),
            'ongoing' => Competition::where('status', 'ongoing')->count(),
            'proofs' => Proof::count(),
            'teams_active' => Team::where('status', 'active')->count(),
            'teams_total' => Team::count(),
        ];
    }

    #[Computed]
    public function recentCompetitions()
    {
        return Competition::latest('id')->limit(5)->get();
    }

    #[Computed]
    public function liveRanking()
    {
        $competitionId = Competition::where('status', 'ongoing')->latest()->value('id')
            ?? Competition::latest()->value('id');

        if (!$competitionId) {
            return collect();
        }

        return TeamProgress::query()
            ->join('teams', 'teams.id', '=', 'team_progress.team_id')
            ->where('teams.competition_id', $competitionId)
            ->whereNull('teams.deleted_at')
            ->selectRaw('
                teams.id, teams.name, teams.color_hex,
                COALESCE(SUM(team_progress.total_score),0) as total_score,
                COALESCE(SUM(team_progress.stages_completed),0) as stages_completed
            ')
            ->groupBy('teams.id', 'teams.name', 'teams.color_hex')
            ->orderByDesc('total_score')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
