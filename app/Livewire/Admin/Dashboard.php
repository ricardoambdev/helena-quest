<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Stage;
use App\Models\Team;
use App\Models\TeamProgress;
use Illuminate\Database\Eloquent\Collection;
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
            'stages' => Stage::count(),
            'teams_active' => Team::where('status', 'active')->count(),
            'teams_total' => Team::count(),
        ];
    }

    #[Computed]
    public function recentActivity(): array
    {
        return TeamProgress::with('team')
            ->whereNotNull('current_stage_id')
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'team_name' => $p->team?->name ?? '?',
                'team_color' => $p->team?->color_hex ?? '#CCCCCC',
                'current_stage_id' => $p->current_stage_id,
                'total_score' => $p->total_score,
                'updated_at' => $p->updated_at?->diffForHumans(),
            ])
            ->toArray();
    }

    #[Computed]
    public function recentCompetitions(): Collection
    {
        return Competition::latest()->limit(5)->get();
    }

    #[Computed]
    public function liveRanking(): Collection
    {
        return TeamProgress::with('team')
            ->selectRaw('team_id, sum(total_score) as total_score, sum(stages_completed) as stages_completed')
            ->groupBy('team_id')
            ->orderByDesc('total_score')
            ->limit(5)
            ->get()
            ->map(fn ($p) => (object) [
                'name' => $p->team?->name ?? '?',
                'color_hex' => $p->team?->color_hex ?? '#CCCCCC',
                'total_score' => $p->total_score,
                'stages_completed' => $p->stages_completed,
            ]);
    }

    public function render(): mixed
    {
        return view('livewire.admin.dashboard');
    }
}
