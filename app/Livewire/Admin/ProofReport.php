<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Stage;
use App\Models\TeamStageProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.admin')]
#[Title('Relatório por Etapas')]
class ProofReport extends Component
{
    public ?int $competitionId = null;

    protected $queryString = [
        'competitionId' => ['except' => null],
    ];

    #[Computed]
    public function competitions()
    {
        return Competition::orderByDesc('year')->get();
    }

    #[Computed]
    public function stages()
    {
        return $this->competitionId
            ? Stage::where('competition_id', $this->competitionId)
                ->with('teamStageProgress')
                ->orderBy('order')
                ->get()
            : collect();
    }

    #[Computed]
    public function competition()
    {
        return $this->competitionId ? Competition::find($this->competitionId) : null;
    }

    public function exportCsv(): StreamedResponse
    {
        $stages = $this->stages();
        if ($stages->isEmpty()) {
            return response()->streamDownload(fn () => '', 'relatorio-vazio.csv');
        }

        return response()->streamDownload(function () use ($stages) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Etapa', 'Tipo', 'Total', 'Completos', 'Ativos', '% Conclusao']);

            foreach ($stages as $stage) {
                $total = $stage->teamStageProgress->count();
                $completed = $stage->teamStageProgress->where('status', 'completed')->count();
                $active = $stage->teamStageProgress->whereIn('status', ['active', 'photo_sent', 'answered_wrong'])->count();
                $pct = $total > 0 ? round(($completed / $total) * 100) : 0;

                fputcsv($handle, [
                    $stage->name,
                    $stage->stage_type,
                    $total,
                    $completed,
                    $active,
                    $pct . '%',
                ]);
            }

            fclose($handle);
        }, 'relatorio-etapas.csv');
    }

    public function render()
    {
        return view('livewire.admin.proof-report', [
            'stages' => $this->stages(),
        ]);
    }
}
