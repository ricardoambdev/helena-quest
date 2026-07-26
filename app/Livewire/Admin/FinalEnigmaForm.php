<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\FinalEnigma;
use App\Models\FinalEnigmaQrCode;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Enigma final')]
class FinalEnigmaForm extends Component
{
    public ?FinalEnigma $enigma = null;
    public ?int $competition_id;

    public string $word = '';
    public int $max_attempts = 3;
    public int $cooldown_minutes = 120;
    public string $description = '';

    /** @var array<int, array<string,string>> */
    public array $qrCodes = [];

    public function mount(?FinalEnigma $enigma = null, ?int $competition_id = null): void
    {
        $this->competition_id = $competition_id;

        if ($enigma?->exists) {
            $this->enigma = $enigma;
            $this->fill([
                'word' => $enigma->word,
                'max_attempts' => $enigma->max_attempts,
                'cooldown_minutes' => $enigma->cooldown_minutes,
                'description' => $enigma->description ?? '',
            ]);
            $this->competition_id = $enigma->competition_id;
            $this->qrCodes = $enigma->qrCodes->map(fn ($c) => [
                'id' => (string) $c->id,
                'letter' => $c->letter,
                'hint_text' => $c->hint_text ?? '',
                'qr_code_uuid' => $c->qr_code_uuid,
            ])->toArray();
        } else {
            $this->qrCodes = [
                ['letter' => '', 'hint_text' => ''],
                ['letter' => '', 'hint_text' => ''],
            ];
        }
    }

    protected function rules(): array
    {
        return [
            'competition_id' => ['required', 'exists:competitions,id', 'unique:final_enigmas,competition_id,' . ($this->enigma?->id ?? 'NULL')],
            'word' => ['required', 'string', 'max:50'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'cooldown_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if (!$this->enigma?->exists) {
            $this->enigma = FinalEnigma::create($data);
        } else {
            $this->enigma->update($data);
        }

        // Sync QR codes
        $existing = collect($this->enigma->qrCodes()->pluck('id')->all());
        $kept = collect($this->qrCodes)->filter(fn ($c) => !empty($c['letter']))->values();

        foreach ($kept as $idx => $c) {
            if (!empty($c['id']) && is_numeric($c['id'])) {
                FinalEnigmaQrCode::where('id', $c['id'])->update([
                    'letter' => mb_strtoupper($c['letter']),
                    'hint_text' => $c['hint_text'] ?? null,
                    'order' => $idx + 1,
                ]);
            } else {
                FinalEnigmaQrCode::create([
                    'final_enigma_id' => $this->enigma->id,
                    'letter' => mb_strtoupper($c['letter']),
                    'hint_text' => $c['hint_text'] ?? null,
                    'order' => $idx + 1,
                ]);
            }
        }

        session()->flash('success', 'Enigma final salvo.');
        $this->redirectRoute('final-enigma.edit', $this->enigma->id);
    }

    public function addLetter(): void
    {
        $this->qrCodes[] = ['letter' => '', 'hint_text' => ''];
    }

    public function removeLetter(int $index): void
    {
        unset($this->qrCodes[$index]);
        $this->qrCodes = array_values($this->qrCodes);
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
