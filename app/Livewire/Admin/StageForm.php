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
#[Title('Editar etapa')]
class StageForm extends Component
{
    public ?Stage $stage = null;
    public ?int $competition_id = null;

    public string $name = '';
    public string $description = '';
    public string $order = '';
    public string $stage_type = 'charada';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public int $radius = 30;
    public string $narrative_text = '';
    public string $correct_answer = '';
    public string $secret_number = '';
    public string $next_stage_hint = '';
    public ?int $score = 50;
    public int $penalty = 0;
    public ?int $time_limit_minutes = null;
    public string $compass_direction = '';
    public ?int $compass_steps = null;
    public string $compass_landmarks = '';
    public string $word = '';
    public int $max_attempts = 5;
    public int $word_score = 50;
    public int $wrong_word_penalty = 10;
    public string $unlock_password = '';

    /** @var array<int, array{id: mixed, question: string, answer: string|null}> */
    public array $subQuestions = [];

    public array $hintsData = [];

    public function mount(?Stage $stage = null, ?int $competition_id = null): void
    {
        $this->competition_id = $competition_id;
        if ($stage?->exists) {
            $this->stage = $stage;
            $this->fill([
                'name' => $stage->name,
                'description' => $stage->description ?? '',
                'order' => (string) $stage->order,
                'stage_type' => $stage->stage_type,
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
                'compass_direction' => $stage->compass_direction ?? '',
                'compass_steps' => $stage->compass_steps,
                'compass_landmarks' => $stage->compass_landmarks ?? '',
                'word' => $stage->word ?? '',
                'max_attempts' => $stage->max_attempts ?? 5,
                'word_score' => $stage->word_score ?? 50,
                'wrong_word_penalty' => $stage->wrong_word_penalty ?? 10,
                'unlock_password' => $stage->unlock_password ?? '',
            ]);
            $this->competition_id = $stage->competition_id;
            $this->subQuestions = $stage->sub_questions ?? [];

            foreach ($stage->hints->sortBy('order') as $hint) {
                $this->hintsData[] = [
                    'id' => $hint->id,
                    'hint_text' => $hint->hint_text,
                    'price' => $hint->price,
                    'order' => $hint->order,
                ];
            }
        } else {
            $this->order = (string) (Stage::where('competition_id', $competition_id)->max('order') + 1);
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

    public function addSubQuestion(): void
    {
        $this->subQuestions[] = ['question' => '', 'answer' => null];
    }

    public function removeSubQuestion(int $index): void
    {
        unset($this->subQuestions[$index]);
        $this->subQuestions = array_values($this->subQuestions);
    }

    protected function rules(): array
    {
        return [
            'competition_id' => ['required', 'exists:competitions,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:1'],
            'stage_type' => ['required', 'in:charada,caca_ao_tesouro,mapas_bussola,enigma_final'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:5', 'max:200'],
            'narrative_text' => ['nullable', 'string'],
            'correct_answer' => ['nullable', 'string', 'max:50'],
            'secret_number' => ['nullable', 'string', 'max:20'],
            'next_stage_hint' => ['nullable', 'string'],
            'score' => ['nullable', 'integer', 'min:0'],
            'penalty' => ['required', 'integer', 'min:0'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'compass_direction' => ['nullable', 'string', 'max:20'],
            'compass_steps' => ['nullable', 'integer', 'min:1'],
            'compass_landmarks' => ['nullable', 'string'],
            'word' => ['nullable', 'string', 'max:50'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:20'],
            'word_score' => ['nullable', 'integer', 'min:0'],
            'wrong_word_penalty' => ['nullable', 'integer', 'min:0'],
            'unlock_password' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            ...$data,
            'sub_questions' => !empty($this->subQuestions) ? $this->subQuestions : null,
            'created_by' => $this->stage?->created_by ?? auth()->id(),
            'updated_by' => auth()->id(),
        ];

        if ($this->stage?->exists) {
            $this->stage->update($payload);
        } else {
            $this->stage = Stage::create($payload);
        }

        $this->syncHints();
        $this->dispatch('stage-saved', stageId: $this->stage->id);
        session()->flash('success', 'Etapa salva.');
        $this->redirectRoute('admin.stages.edit', $this->stage->id);
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
        if (!$this->stage?->exists) return;
        $this->stage->delete();
        session()->flash('success', 'Etapa excluida.');
        $this->redirectRoute('admin.competitions.edit', $this->competition_id);
    }

    public function regenerateQr(): void
    {
        if (!$this->stage?->exists) return;
        $this->stage->update(['qr_code_uuid' => (string) \Illuminate\Support\Str::uuid(), 'updated_by' => auth()->id()]);
        $this->stage->refresh();
        session()->flash('success', 'QR Code regenerado.');
    }

    #[Computed]
    public function competition()
    {
        return $this->competition_id ? Competition::find($this->competition_id) : null;
    }

    #[Computed]
    public function stages()
    {
        return $this->competition_id
            ? Stage::where('competition_id', $this->competition_id)->orderBy('order')->get()
            : collect();
    }

    public function render()
    {
        return view('livewire.admin.stage-form');
    }
}
