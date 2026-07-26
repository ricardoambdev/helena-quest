<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Proof;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Provas')]
class ProofIndex extends Component
{
    public ?int $competitionId = null;

    #[Computed]
    public function competitionsList()
    {
        return \App\Models\Competition::orderByDesc('year')->get();
    }

    #[Computed]
    public function proofs()
    {
        return Proof::query()
            ->with('competition')
            ->withCount('stages')
            ->when($this->competitionId, fn ($q) => $q->where('competition_id', $this->competitionId))
            ->orderBy('order')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.proof-index');
    }
}
