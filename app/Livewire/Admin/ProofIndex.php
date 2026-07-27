<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Component;

class ProofIndex extends Component
{
    public function mount(): void
    {
        $this->redirectRoute('admin.stages.index');
    }

    public function render(): mixed
    {
        return view('livewire.admin.proof-index');
    }
}
