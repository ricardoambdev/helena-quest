<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.admin')]
#[Title('Relatório por Competição')]
class CompetitionReport extends Component
{
    public ?int $competitionId = null;

    protected $queryString = [
        'competitionId' => ['except' => null],
    ];

    #[Computed]
    public function competitions()
    {
        return Competition::orderByDesc('date')->get();
    }

    #[Computed]
    public function competition()
    {
        return $this->competitionId ? Competition::with(['proofs.stages', 'teams.progress'])->find($this->competitionId) : null;
    }

    public function reportData(): ?array
    {
        $c = $this->competition;
        if (!$c) return null;

        $totalTeams = $c->teams->count();
        $proofs = $c->proofs->sortBy('order');
        $totalStages = $proofs->sum(fn ($p) => $p->stages->count());

        $teamRows = $c->teams->map(function ($team) use ($proofs) {
            $progress = $team->progress->keyBy('proof_id');
            $completed = $team->stageProgress()->where('status', 'completed')->count();
            $totalScore = (int) $progress->sum('total_score');
            $photos = (int) $progress->sum('photos_count');
            $audios = (int) $team->audios()->count();
            $hints = (int) $progress->sum('hints_bought');
            $time = (int) $progress->sum('total_time_seconds');

            return [
                'name' => $team->name,
                'color' => $team->color_hex,
                'stages_completed' => $completed,
                'total_score' => $totalScore,
                'photos' => $photos,
                'audios' => $audios,
                'hints' => $hints,
                'time_seconds' => $time,
            ];
        })->sortByDesc('total_score')->values();

        return [
            'competition_name' => $c->name,
            'total_teams' => $totalTeams,
            'total_stages' => $totalStages,
            'teams' => $teamRows,
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $data = $this->reportData();
        if (!$data) {
            return response()->streamDownload(fn () => '', 'relatorio-vazio.csv');
        }

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Equipe', 'Etapas', 'Pontuação', 'Fotos', 'Áudios', 'Dicas', 'Tempo']);

            foreach ($data['teams'] as $row) {
                $minutes = intdiv($row['time_seconds'], 60);
                $secs = $row['time_seconds'] % 60;
                fputcsv($handle, [
                    $row['name'],
                    $row['stages_completed'],
                    $row['total_score'],
                    $row['photos'],
                    $row['audios'],
                    $row['hints'],
                    sprintf('%dm%02ds', $minutes, $secs),
                ]);
            }

            fclose($handle);
        }, 'relatorio-competicao.csv');
    }

    public function render()
    {
        return view('livewire.admin.competition-report', [
            'data' => $this->reportData(),
        ]);
    }
}
