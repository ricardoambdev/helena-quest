<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Competições')]
class CompetitionIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $yearFilter = '';
    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'yearFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function resetFilters()
    {
        $this->reset(['search', 'yearFilter', 'statusFilter']);
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $c = Competition::findOrFail($id);
        if (in_array($c->status, ['ongoing', 'paused', 'finished'], true)) {
            session()->flash('error', 'Não é possível excluir competição já em andamento ou encerrada.');
            return;
        }
        $c->delete();
        session()->flash('success', 'Competição removida.');
    }

    public function render()
    {
        $rows = Competition::query()
            ->withCount('proofs')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->yearFilter, fn ($q) => $q->where('year', $this->yearFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest('id')
            ->paginate(15);

        return view('livewire.admin.competition-index', ['competitions' => $rows]);
    }
}
