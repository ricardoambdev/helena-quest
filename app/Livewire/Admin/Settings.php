<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Configurações — Helena Quest')]
class Settings extends Component
{
    public function render()
    {
        return view('livewire.admin.settings');
    }
}
