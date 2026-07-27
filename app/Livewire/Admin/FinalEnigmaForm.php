<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\BonusOnus;
use App\Models\Stage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Enigma final')]
class FinalEnigmaForm extends Component
{
    public ?Stage $stage = null;
    public ?int $competition_id = null;

    public string $name = '';
    public string $narrative_text = '';
    public string $word = '';
    public int $max_attempts = 5;
    public int $word_score = 50;
    public int $wrong_word_penalty = 10;

    /** @var array<int, array{id: string|null, name: string, points: int, qr_code_uuid: string|null}> */
    public array $cofres = [];

    public function mount(?Stage $stage = null, ?int $competition_id = null): void
    {
        $this->competition_id = $competition_id;

        if ($stage?->exists && $stage->stage_type === 'enigma_final') {
            $this->stage = $stage;
            $this->fill([
                'name' => $stage->name,
                'narrative_text' => $stage->narrative_text ?? '',
                'word' => $stage->word ?? '',
                'max_attempts' => $stage->max_attempts ?? 5,
                'word_score' => $stage->word_score ?? 50,
                'wrong_word_penalty' => $stage->wrong_word_penalty ?? 10,
            ]);
            $this->competition_id = $stage->competition_id;
            $this->cofres = $stage->bonusOnus->map(fn ($b) => [
                'id' => (string) $b->id,
                'name' => $b->name,
                'points' => $b->points,
                'qr_code_uuid' => $b->qr_code_uuid,
            ])->toArray();
        } else {
            $this->cofres = [
                ['id' => null, 'name' => '', 'points' => 10, 'qr_code_uuid' => null],
                ['id' => null, 'name' => '', 'points' => 10, 'qr_code_uuid' => null],
            ];
        }
    }

    protected function rules(): array
    {
        return [
            'competition_id' => ['required', 'exists:competitions,id'],
            'name' => ['required', 'string', 'max:255'],
            'narrative_text' => ['nullable', 'string'],
            'word' => ['required', 'string', 'max:50'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'word_score' => ['required', 'integer', 'min:0'],
            'wrong_word_penalty' => ['required', 'integer', 'min:0'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $payload = [
            'competition_id' => $this->competition_id,
            'name' => $this->name,
            'stage_type' => 'enigma_final',
            'order' => $this->stage?->order ?? (Stage::where('competition_id', $this->competition_id)->max('order') + 1),
            'narrative_text' => $this->narrative_text,
            'word' => $this->word,
            'max_attempts' => $this->max_attempts,
            'word_score' => $this->word_score,
            'wrong_word_penalty' => $this->wrong_word_penalty,
            'updated_by' => auth()->id(),
        ];

        if ($this->stage?->exists) {
            $this->stage->update($payload);
        } else {
            $payload['created_by'] = auth()->id();
            $this->stage = Stage::create($payload);
        }

        $this->syncCofres();

        session()->flash('success', 'Enigma final salvo.');
        $this->redirectRoute('admin.final-enigma.edit', $this->stage->id);
    }

    private function syncCofres(): void
    {
        $existingIds = $this->stage->bonusOnus()->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($this->cofres as $c) {
            if (empty($c['name'])) continue;

            $data = [
                'stage_id' => $this->stage->id,
                'type' => 'cofre',
                'name' => $c['name'],
                'points' => (int) ($c['points'] ?? 10),
                'qr_code_uuid' => $c['qr_code_uuid'] ?? (string) Str::uuid(),
            ];

            if (!empty($c['id'])) {
                BonusOnus::where('id', $c['id'])->update($data);
                $submittedIds[] = (int) $c['id'];
            } else {
                $bonus = BonusOnus::create($data);
                $submittedIds[] = $bonus->id;
            }
        }

        $toDelete = array_diff($existingIds, $submittedIds);
        if (!empty($toDelete)) {
            BonusOnus::whereIn('id', $toDelete)->delete();
        }
    }

    public function addCofre(): void
    {
        $this->cofres[] = ['id' => null, 'name' => '', 'points' => 10, 'qr_code_uuid' => null];
    }

    public function removeCofre(int $index): void
    {
        if (!isset($this->cofres[$index])) return;
        $c = $this->cofres[$index];
        if ($c['id']) {
            BonusOnus::find($c['id'])?->delete();
        }
        unset($this->cofres[$index]);
        $this->cofres = array_values($this->cofres);
    }

    public function regenerateCofreQr(int $index): void
    {
        if (!isset($this->cofres[$index])) return;
        $this->cofres[$index]['qr_code_uuid'] = (string) Str::uuid();
        if (!empty($this->cofres[$index]['id'])) {
            BonusOnus::where('id', $this->cofres[$index]['id'])
                ->update(['qr_code_uuid' => $this->cofres[$index]['qr_code_uuid']]);
        }
    }

    #[Computed]
    public function competitionsList()
    {
        return Competition::orderByDesc('year')->get();
    }

    public function render()
    {
        return view('livewire.admin.final-enigma-form');
    }
}
