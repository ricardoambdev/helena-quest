<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Editar competição')]
class CompetitionForm extends Component
{
    public ?Competition $competition = null;

    public string $name = '';
    public string $description = '';
    public string $year = '';
    public string $date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $status = 'planning';
    public int $max_teams = 2;
    public string $rules_markdown = '';

    public function mount(?Competition $competition = null): void
    {
        if ($competition?->exists) {
            $this->competition = $competition;
            $this->fill([
                'name' => $competition->name,
                'description' => $competition->description ?? '',
                'year' => (string) $competition->year,
                'date' => $competition->date?->format('Y-m-d') ?? '',
                'start_time' => $competition->start_time?->format('Y-m-d H:i') ?? '',
                'end_time' => $competition->end_time?->format('Y-m-d H:i') ?? '',
                'status' => $competition->status,
                'max_teams' => $competition->max_teams ?? 2,
                'rules_markdown' => $competition->rules_markdown ?? '',
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('competitions', 'name')
                ->ignore($this->competition?->id)
                ->where('year', $this->year)],
            'description' => ['nullable', 'string'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'status' => ['required', 'in:planning,published,ongoing,paused,finished,archived'],
            'max_teams' => ['required', 'integer', 'min:1', 'max:999'],
            'rules_markdown' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            ...$data,
            'created_by' => $this->competition?->created_by ?? auth()->id(),
            'updated_by' => auth()->id(),
        ];

        if ($this->competition?->exists) {
            $previous = $this->competition->status;
            $this->competition->update($payload);

            if ($previous !== $this->competition->status) {
                event(new \App\Events\CompetitionStatusChanged($this->competition->fresh(), $previous));
            }
        } else {
            $created = Competition::create($payload);
            $this->competition = $created;
        }

        session()->flash('success', $this->competition->wasRecentlyCreated ? 'Competição criada.' : 'Competição atualizada.');

        $this->redirectRoute('admin.competitions.edit', $this->competition->id);
    }

    public function publish(): void
    {
        if (!$this->competition?->exists) {
            session()->flash('error', 'Salve a competição antes de publicar.');
            return;
        }

        if ($this->competition->proofs()->count() === 0) {
            session()->flash('error', 'Adicione ao menos uma prova para publicar.');
            return;
        }

        $previous = $this->competition->status;
        $this->competition->update(['status' => 'published', 'updated_by' => auth()->id()]);
        event(new \App\Events\CompetitionStatusChanged($this->competition->fresh(), $previous));

        session()->flash('success', 'Competição publicada.');
        $this->redirectRoute('admin.competitions.edit', $this->competition->id);
    }

    public function startCompetition(): void
    {
        if (!$this->competition?->exists) {
            return;
        }

        $previous = $this->competition->status;
        $this->competition->update([
            'status' => 'ongoing',
            'started_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        event(new \App\Events\CompetitionStatusChanged($this->competition->fresh(), $previous));

        session()->flash('success', 'Competição iniciada. Boas gincanas!');
        $this->redirectRoute('admin.competitions.edit', $this->competition->id);
    }

    public function pause(): void
    {
        if (!$this->competition?->exists || $this->competition->status !== 'ongoing') {
            return;
        }
        $previous = $this->competition->status;
        $this->competition->update(['status' => 'paused', 'updated_by' => auth()->id()]);
        event(new \App\Events\CompetitionStatusChanged($this->competition->fresh(), $previous));
        session()->flash('success', 'Competição pausada.');
        $this->redirectRoute('admin.competitions.edit', $this->competition->id);
    }

    public function finish(): void
    {
        if (!$this->competition?->exists) {
            return;
        }
        $previous = $this->competition->status;
        $this->competition->update([
            'status' => 'finished',
            'finished_at' => now(),
            'updated_by' => auth()->id(),
        ]);
        event(new \App\Events\CompetitionStatusChanged($this->competition->fresh(), $previous));
        session()->flash('success', 'Competição encerrada.');
        $this->redirectRoute('admin.competitions.edit', $this->competition->id);
    }

    #[Computed]
    public function proofs()
    {
        return $this->competition?->proofs()->orderBy('order')->get() ?? collect();
    }

    public function render()
    {
        return view('livewire.admin.competition-form');
    }
}
