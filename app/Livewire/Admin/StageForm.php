<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Stage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Editar etapa')]
class StageForm extends Component
{
    public ?Stage $stage = null;
    public ?int $proof_id;

    public string $name = '';
    public string $description = '';
    public string $order = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public int $radius = 30;
    public string $narrative_text = '';
    public string $correct_answer = '';
    public string $secret_number = '';
    public string $next_stage_hint = '';
    public ?int $score = 100;
    public int $penalty = 0;
    public ?int $time_limit_minutes = null;

    public array $hintsData = [];

    public function mount(?Stage $stage = null, ?int $proof_id = null): void
    {
        $this->proof_id = $proof_id;
        if ($stage?->exists) {
            $this->stage = $stage;
            $this->fill([
                'name' => $stage->name,
                'description' => $stage->description ?? '',
                'order' => (string) $stage->order,
                'latitude' => $stage->latitude !== null ? (float) $stage->latitude : null,
                'longitude' => $stage->longitude !== null ? (float) $stage->longitude : null,
                'radius' => $stage->radius,
                'narrative_text' => $stage->narrative_text,
                'correct_answer' => $stage->correct_answer,
                'secret_number' => $stage->secret_number,
                'next_stage_hint' => $stage->next_stage_hint ?? '',
                'score' => $stage->score,
                'penalty' => $stage->penalty,
                'time_limit_minutes' => $stage->time_limit_minutes,
            ]);
            $this->proof_id = $stage->proof_id;

            foreach ($stage->hints->sortBy('order') as $hint) {
                $this->hintsData[] = [
                    'id' => $hint->id,
                    'hint_text' => $hint->hint_text,
                    'price' => $hint->price,
                    'order' => $hint->order,
                ];
            }
        } else {
            $this->order = (string) (\App\Models\Stage::where('proof_id', $proof_id)->max('order') + 1);
        }
    }

    public function addHint(): void
    {
        $this->hintsData[] = [
            'id' => null,
            'hint_text' => '',
            'price' => 0,
            'order' => count($this->hintsData) + 1,
        ];
    }

    public function removeHint(int $index): void
    {
        if (!isset($this->hintsData[$index])) return;

        $hint = $this->hintsData[$index];
        if ($hint['id']) {
            \App\Models\Hint::find($hint['id'])?->delete();
        }

        unset($this->hintsData[$index]);
        $this->hintsData = array_values($this->hintsData);

        foreach ($this->hintsData as $i => &$h) {
            $h['order'] = $i + 1;
        }
    }

    protected function rules(): array
    {
        return [
            'proof_id' => ['required', 'exists:proofs,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:1'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:5', 'max:200'],
            'narrative_text' => ['required', 'string'],
            'correct_answer' => ['required', 'string', 'regex:/^\d{4,8}$/'],
            'secret_number' => ['required', 'string', 'regex:/^\d{4,8}$/'],
            'next_stage_hint' => ['nullable', 'string'],
            'score' => ['nullable', 'integer', 'min:0'],
            'penalty' => ['required', 'integer', 'min:0'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            ...$data,
            'created_by' => $this->stage?->created_by ?? auth()->id(),
            'updated_by' => auth()->id(),
        ];

        if ($this->stage?->exists) {
            $this->stage->update($payload);
        } else {
            $this->stage = Stage::create($payload);
        }

        $this->syncHints();

        $this->proof_id = $this->stage->proof_id;
        $this->dispatch('stage-saved', stageId: $this->stage->id);

        session()->flash('success', 'Etapa salva.');
        $this->redirectRoute('stages.edit', $this->stage->id);
    }

    private function syncHints(): void
    {
        $existingIds = $this->stage->hints()->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($this->hintsData as $item) {
            $data = [
                'stage_id' => $this->stage->id,
                'hint_text' => $item['hint_text'],
                'price' => (int) ($item['price'] ?? 0),
                'order' => (int) ($item['order'] ?? 0),
            ];

            if (!empty($item['id'])) {
                \App\Models\Hint::where('id', $item['id'])->update($data);
                $submittedIds[] = (int) $item['id'];
            } else {
                $hint = \App\Models\Hint::create($data);
                $submittedIds[] = $hint->id;
            }
        }

        $toDelete = array_diff($existingIds, $submittedIds);
        if (!empty($toDelete)) {
            \App\Models\Hint::whereIn('id', $toDelete)->delete();
        }
    }

    public function delete(): void
    {
        if (!$this->stage?->exists) {
            return;
        }
        $this->stage->delete();
        session()->flash('success', 'Etapa excluída.');
        $this->redirectRoute('proofs.edit', $this->proof_id);
    }

    public function regenerateQr(): void
    {
        if (!$this->stage?->exists) {
            return;
        }
        $this->stage->update(['qr_code_uuid' => (string) \Illuminate\Support\Str::uuid(), 'updated_by' => auth()->id()]);
        $this->stage->refresh();
        session()->flash('success', 'QR Code regenerado.');
    }

    #[Computed]
    public function proof()
    {
        return $this->proof_id ? \App\Models\Proof::find($this->proof_id) : null;
    }

    #[Computed]
    public function stages()
    {
        return $this->proof_id ? \App\Models\Stage::where('proof_id', $this->proof_id)->orderBy('order')->get() : collect();
    }

    public function render()
    {
        return view('livewire.admin.stage-form');
    }
}
