<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Proof;
use App\Models\Stage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Etapas')]
class StageIndex extends Component
{
    public ?int $proofFilter = null;

    #[Computed]
    public function proofs()
    {
        return Proof::with('competition')->orderBy('competition_id')->orderBy('order')->get();
    }

    #[Computed]
    public function stages()
    {
        return Stage::query()
            ->with(['proof.competition'])
            ->when($this->proofFilter, fn ($q) => $q->where('proof_id', $this->proofFilter))
            ->orderBy('proof_id')->orderBy('order')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.stage-index');
    }
}
