<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Team;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Equipe')]
class TeamForm extends Component
{
    public ?Team $team = null;
    public ?int $competition_id;

    public string $name = '';
    public string $color_hex = '';
    public string $username = '';
    public string $password = '';
    public string $status = 'active';
    public string $description = '';

    public function mount(?Team $team = null, ?int $competition_id = null): void
    {
        $this->competition_id = $competition_id;
        if ($team?->exists) {
            $this->team = $team;
            $this->fill([
                'name' => $team->name,
                'color_hex' => $team->color_hex,
                'username' => $team->username,
                'password' => '',
                'status' => $team->status,
                'description' => $team->description ?? '',
            ]);
            $this->competition_id = $team->competition_id;
        }
    }

    protected function rules(): array
    {
        return [
            'competition_id' => ['required', 'exists:competitions,id'],
            'name' => ['required', 'string', 'max:255'],
            'color_hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'username' => ['required', 'string', 'max:255', 'unique:teams,username,' . ($this->team?->id ?? 'NULL')],
            'password' => [$this->team?->exists ? 'nullable' : 'required', 'string', 'min:6'],
            'status' => ['required', 'in:active,blocked,inactive,eliminated'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            'competition_id' => $data['competition_id'],
            'name' => $data['name'],
            'color_hex' => $data['color_hex'],
            'username' => $data['username'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password_hash'] = Hash::make($data['password']);
            $payload['password_changed_at'] = now();
            $payload['password_changed_by'] = auth()->id();
        }

        if ($this->team?->exists) {
            $this->team->update($payload);
            session()->flash('success', 'Equipe atualizada.');
        } else {
            $this->team = Team::create($payload);
            session()->flash('success', 'Equipe criada. Usuário: ' . $this->team->username);
        }

        $this->redirectRoute('teams.edit', $this->team->id);
    }

    public function block(): void
    {
        $this->team->update(['status' => 'blocked', 'updated_by' => auth()->id()]);
        $this->status = 'blocked';
        $this->team->tokens()->delete();
        session()->flash('success', 'Equipe bloqueada e sessões encerradas.');
    }

    public function unblock(): void
    {
        $this->team->update(['status' => 'active', 'updated_by' => auth()->id()]);
        $this->status = 'active';
        session()->flash('success', 'Equipe desbloqueada.');
    }

    public function delete(): void
    {
        if (!$this->team?->exists) {
            return;
        }
        $this->team->delete();
        session()->flash('success', 'Equipe excluída (soft delete).');
        $this->redirectRoute('teams.index');
    }

    #[Computed]
    public function competitionsList()
    {
        return Competition::orderBy('year', 'desc')->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.admin.team-form');
    }
}
