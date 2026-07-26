<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AuthenticationLog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Auditoria')]
class LogsIndex extends Component
{
    use WithPagination;

    public ?int $teamFilter;
    public string $actionFilter = '';
    public string $successFilter = '';

    protected $queryString = [
        'teamFilter' => ['except' => ''],
        'actionFilter' => ['except' => ''],
        'successFilter' => ['except' => ''],
    ];

    #[Computed]
    public function logs()
    {
        return AuthenticationLog::query()
            ->with('team')
            ->when($this->teamFilter, fn ($q) => $q->where('team_id', $this->teamFilter))
            ->when($this->actionFilter, fn ($q) => $q->where('action', $this->actionFilter))
            ->when($this->successFilter !== '', fn ($q) => $q->where('success', filter_var($this->successFilter, FILTER_VALIDATE_BOOL)))
            ->latest('created_at')
            ->paginate(40);
    }

    #[Computed]
    public function teamsList()
    {
        return \App\Models\Team::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.admin.logs-index');
    }
}
