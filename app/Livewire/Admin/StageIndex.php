<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Stage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Etapas')]
class StageIndex extends Component
{
    public ?int $competitionFilter = null;

    #[Computed]
    public function competitions()
    {
        return Competition::orderBy('name')->get();
    }

    #[Computed]
    public function stages()
    {
        return Stage::query()
            ->with('competition')
            ->when($this->competitionFilter, fn ($q) => $q->where('competition_id', $this->competitionFilter))
            ->orderBy('competition_id')
            ->orderBy('order')
            ->get();
    }

    public function render(): mixed
    {
        return view('livewire.admin.stage-index');
    }
}
