<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Proof;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Editar prova')]
class ProofForm extends Component
{
    public ?Proof $proof = null;
    public ?int $competition_id;

    public string $name = '';
    public string $description = '';
    public string $order = '';
    public string $status = 'configuring';
    public ?string $color_hex = '#FF6600';

    public function mount(?Proof $proof = null, ?int $competition_id = null): void
    {
        $this->competition_id = $competition_id;
        if ($proof?->exists) {
            $this->proof = $proof;
            $this->fill([
                'name' => $proof->name,
                'description' => $proof->description ?? '',
                'order' => (string) $proof->order,
                'status' => $proof->status,
                'color_hex' => $proof->color_hex ?? '#FF6600',
            ]);
            $this->competition_id = $proof->competition_id;
        } else {
            $this->order = (string) (Proof::where('competition_id', $competition_id)->max('order') + 1);
        }
    }

    protected function rules(): array
    {
        return [
            'competition_id' => ['required', 'exists:competitions,id'],
            'name' => ['required', 'string', 'max:255', 'unique:proofs,name,NULL,id,competition_id,' . $this->competition_id],
            'description' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:configuring,active,inactive,finished'],
            'color_hex' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            ...$data,
            'created_by' => $this->proof?->created_by ?? auth()->id(),
            'updated_by' => auth()->id(),
        ];

        if ($this->proof?->exists) {
            $this->proof->update($payload);
        } else {
            $this->proof = Proof::create($payload);
        }

        $this->competition_id = $this->proof->competition_id;
        session()->flash('success', 'Prova salva. Agora adicione as etapas.');
        $this->redirectRoute('admin.proofs.edit', $this->proof->id);
    }

    public function delete(): void
    {
        if (!$this->proof?->exists) {
            return;
        }
        if ($this->proof->status !== 'configuring') {
            session()->flash('error', 'Só é possível excluir provas em configuração.');
            return;
        }
        $this->proof->delete();
        session()->flash('success', 'Prova excluída.');
        $this->redirectRoute('admin.competitions.edit', $this->competition_id);
    }

    #[Computed]
    public function competition()
    {
        return Competition::find($this->competition_id);
    }

    #[Computed]
    public function stages()
    {
        return $this->proof?->stages ?? collect();
    }

    public function render()
    {
        return view('livewire.admin.proof-form');
    }
}
