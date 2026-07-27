<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Monitor de equipes')]
class TeamMonitor extends Component
{
    public ?int $teamId = null;
    public ?int $competitionFilter = null;

    #[Computed]
    public function teams()
    {
        return Team::query()
            ->with(['competition', 'progress'])
            ->when($this->competitionFilter, fn ($q) => $q->where('competition_id', $this->competitionFilter))
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedTeam()
    {
        return $this->teamId ? Team::with(['stageProgress.stage', 'progress'])->find($this->teamId) : null;
    }

    public function render()
    {
        return view('livewire.admin.team-monitor');
    }
}
