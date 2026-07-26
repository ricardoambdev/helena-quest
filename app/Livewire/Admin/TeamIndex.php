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
#[Title('Equipes')]
class TeamIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $competitionFilter = null;
    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'competitionFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    #[Computed]
    public function competitionsList()
    {
        return \App\Models\Competition::orderByDesc('year')->get();
    }

    #[Computed]
    public function teams()
    {
        return Team::query()
            ->with('competition')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%");
            }))
            ->when($this->competitionFilter, fn ($q) => $q->where('competition_id', $this->competitionFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('name')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.team-index');
    }
}
