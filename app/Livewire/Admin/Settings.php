<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\SystemPreference;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
#[Title('Configurações — Helena Quest')]
class Settings extends Component
{
    use WithFileUploads;

    public array $preferences = [];
    public $schoolLogo;

    protected function rules(): array
    {
        return [
            'preferences.school_name' => 'nullable|string|max:200',
            'preferences.school_address' => 'nullable|string|max:300',
            'preferences.school_latitude' => 'nullable|numeric|between:-90,90',
            'preferences.school_longitude' => 'nullable|numeric|between:-180,180',
            'preferences.telao_map_zoom' => 'nullable|integer|min:1|max:19',
            'preferences.telao_refresh_seconds' => 'nullable|integer|min:1|max:60',
            'schoolLogo' => 'nullable|image|max:2048',
        ];
    }

    public function mount(): void
    {
        $this->loadPreferences();
    }

    public function loadPreferences(): void
    {
        $keys = [
            'school_name', 'school_address', 'school_latitude', 'school_longitude',
            'school_logo_path',
            'telao_map_zoom', 'telao_refresh_seconds',
        ];

        foreach ($keys as $key) {
            $this->preferences[$key] = SystemPreference::getValue($key, '');
        }
    }

    public function updatedSchoolLogo(): void
    {
        $this->validateOnly('schoolLogo');
    }

    public function save(): void
    {
        $this->validate();

        if ($this->schoolLogo) {
            $path = $this->schoolLogo->store('system', 'public');
            $this->preferences['school_logo_path'] = $path;
            SystemPreference::setValue('school_logo_path', $path);
        }

        $numericKeys = ['school_latitude', 'school_longitude', 'telao_map_zoom', 'telao_refresh_seconds'];

        foreach ($this->preferences as $key => $value) {
            if ($key === 'school_logo_path' && !$this->schoolLogo) {
                continue;
            }
            $type = in_array($key, $numericKeys, true) ? 'float' : 'string';
            if ($key === 'telao_map_zoom' || $key === 'telao_refresh_seconds') {
                $type = 'integer';
            }
            SystemPreference::setValue($key, $value, $type);
        }

        $this->loadPreferences();
        $this->schoolLogo = null;

        session()->flash('message', 'Configurações salvas com sucesso!');
    }

    public function removeLogo(): void
    {
        $current = SystemPreference::getValue('school_logo_path');
        if ($current && Storage::disk('public')->exists($current)) {
            Storage::disk('public')->delete($current);
        }
        SystemPreference::setValue('school_logo_path', '');
        $this->preferences['school_logo_path'] = '';
        $this->schoolLogo = null;
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
