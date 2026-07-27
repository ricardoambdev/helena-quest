<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.admin')]
#[Title('Relatorio por Equipe')]
class TeamReport extends Component
{
    public ?int $teamId = null;

    protected $queryString = [
        'teamId' => ['except' => null],
    ];

    #[Computed]
    public function teams()
    {
        return Team::with('competition')->orderBy('name')->get();
    }

    public function team()
    {
        return $this->teamId ? Team::with([
            'competition',
            'stageProgress.stage',
            'audios',
            'progress',
        ])->find($this->teamId) : null;
    }

    public function exportCsv(): StreamedResponse
    {
        $team = $this->team();
        if (!$team) {
            return response()->streamDownload(fn () => '', 'relatorio-vazio.csv');
        }

        return response()->streamDownload(function () use ($team) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Etapa', 'Tipo', 'Status', 'Pontuacao', 'Tempo (s)', 'Tentativas']);

            foreach ($team->stageProgress()->with('stage')->orderBy('stage_id')->get() as $sp) {
                fputcsv($handle, [
                    $sp->stage?->name ?? '—',
                    $sp->stage?->stage_type ?? '—',
                    $sp->status,
                    $sp->score_earned ?? 0,
                    $sp->time_spent_seconds ?? 0,
                    $sp->attempts_count ?? 0,
                ]);
            }

            fclose($handle);
        }, 'relatorio-equipe-' . $team->id . '.csv');
    }

    public function render()
    {
        return view('livewire.admin.team-report', [
            'team' => $this->team(),
        ]);
    }
}
