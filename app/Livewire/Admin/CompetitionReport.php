<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Stage;
use App\Models\TeamProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.admin')]
#[Title('Relatorio por Competicao')]
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
        return $this->competitionId ? Competition::with('stages', 'teams')->find($this->competitionId) : null;
    }

    public function reportData(): ?array
    {
        $c = $this->competition;
        if (!$c) return null;

        $totalTeams = $c->teams->count();
        $totalStages = $c->stages->count();
        $teamIds = $c->teams->pluck('id');

        $teamProgress = TeamProgress::whereIn('team_id', $teamIds)
            ->selectRaw('team_id, SUM(total_score) as total_score, SUM(stages_completed) as total_stages, SUM(photos_count) as photos_count, SUM(correct_answers) as correct, SUM(hints_bought) as hints')
            ->groupBy('team_id')
            ->get()
            ->keyBy('team_id');

        $teamRows = $c->teams->map(function ($team) use ($teamProgress) {
            $p = $teamProgress->get($team->id);

            return [
                'name' => $team->name,
                'color' => $team->color_hex,
                'stages_completed' => (int) ($p?->total_stages ?? 0),
                'total_score' => (int) ($p?->total_score ?? 0),
                'correct_answers' => (int) ($p?->correct ?? 0),
                'photos' => (int) ($p?->photos_count ?? 0),
                'hints' => (int) ($p?->hints ?? 0),
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
            fputcsv($handle, ['Equipe', 'Etapas', 'Pontuacao', 'Corretas', 'Fotos', 'Dicas']);

            foreach ($data['teams'] as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['stages_completed'],
                    $row['total_score'],
                    $row['correct_answers'],
                    $row['photos'],
                    $row['hints'],
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
