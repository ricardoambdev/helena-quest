<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Proof;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.admin')]
#[Title('Relatório por Prova')]
class ProofReport extends Component
{
    public ?int $proofId = null;

    protected $queryString = [
        'proofId' => ['except' => null],
    ];

    #[Computed]
    public function proofs()
    {
        return Proof::with('competition')->orderBy('competition_id')->orderBy('order')->get();
    }

    public function proof()
    {
        return $this->proofId ? Proof::with([
            'competition',
            'stages' => fn ($q) => $q->orderBy('order'),
            'stages.teamStageProgress',
        ])->find($this->proofId) : null;
    }

    public function exportCsv(): StreamedResponse
    {
        $p = $this->proof();
        if (!$p) {
            return response()->streamDownload(fn () => '', 'relatorio-vazio.csv');
        }

        return response()->streamDownload(function () use ($p) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Etapa', 'Total', 'Completos', 'Ativos', '% Conclusão']);

            foreach ($p->stages as $stage) {
                $total = $stage->teamStageProgress->count();
                $completed = $stage->teamStageProgress->where('status', 'completed')->count();
                $active = $stage->teamStageProgress->whereIn('status', ['active', 'photo_sent', 'answered_wrong'])->count();
                $pct = $total > 0 ? round(($completed / $total) * 100) : 0;

                fputcsv($handle, [
                    $stage->name,
                    $total,
                    $completed,
                    $active,
                    $pct . '%',
                ]);
            }

            fclose($handle);
        }, 'relatorio-prova-' . $p->id . '.csv');
    }

    public function render()
    {
        return view('livewire.admin.proof-report', [
            'proof' => $this->proof(),
        ]);
    }
}
