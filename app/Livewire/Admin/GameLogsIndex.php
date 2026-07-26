<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.admin')]
#[Title('Logs do Jogo')]
class GameLogsIndex extends Component
{
    use WithPagination;

    public ?int $teamFilter = null;
    public string $actionFilter = '';
    public string $successFilter = '';

    protected $queryString = [
        'teamFilter' => ['except' => null],
        'actionFilter' => ['except' => ''],
        'successFilter' => ['except' => ''],
    ];

    #[Computed]
    public function teams()
    {
        return \App\Models\Team::orderBy('name')->get();
    }

    #[Computed]
    public function actions(): array
    {
        return [
            'stage_started', 'qr_scan_failed', 'gps_out_of_range',
            'photo_sent', 'answer_correct', 'answer_wrong',
            'hint_bought', 'audio_uploaded',
            'final_letter_scanned', 'final_enigma_guess', 'final_enigma_solved',
        ];
    }

    public function logs()
    {
        return AuditLog::query()
            ->with('team')
            ->when($this->teamFilter, fn ($q) => $q->where('team_id', $this->teamFilter))
            ->when($this->actionFilter, fn ($q) => $q->where('action', $this->actionFilter))
            ->latest('created_at')
            ->paginate(40);
    }

    public function exportCsv(): StreamedResponse
    {
        $query = AuditLog::query()
            ->with('team')
            ->when($this->teamFilter, fn ($q) => $q->where('team_id', $this->teamFilter))
            ->when($this->actionFilter, fn ($q) => $q->where('action', $this->actionFilter))
            ->latest('created_at');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Data', 'Equipe', 'Ação', 'Contexto', 'IP']);

            $query->chunk(200, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->created_at?->toIso8601String(),
                        $log->team?->name ?? '—',
                        $log->action,
                        $log->context ? json_encode($log->context, JSON_UNESCAPED_UNICODE) : '',
                        $log->ip ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 'audit-logs.csv');
    }

    public function render()
    {
        return view('livewire.admin.game-logs-index', [
            'logs' => $this->logs(),
        ]);
    }
}
