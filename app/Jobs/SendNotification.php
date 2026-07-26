<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Team;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    public function __construct(
        public readonly Team $team,
        public readonly string $title,
        public readonly string $body,
        public readonly ?array $data = null,
    ) {}

    public function handle(): void
    {
        // TODO: integrar com Firebase Cloud Messaging ou OneSignal quando disponível
        Log::info('SendNotification', [
            'team_id' => $this->team->id,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ]);
    }
}
