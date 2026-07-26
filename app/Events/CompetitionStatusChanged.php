<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Competition;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetitionStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Competition $competition,
        public readonly string $previousStatus,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("competition.{$this->competition->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'competition.status';
    }

    public function broadcastWith(): array
    {
        return [
            'competition_id' => $this->competition->id,
            'name' => $this->competition->name,
            'previous_status' => $this->previousStatus,
            'current_status' => $this->competition->status,
            'started_at' => $this->competition->started_at?->toIso8601String(),
            'finished_at' => $this->competition->finished_at?->toIso8601String(),
        ];
    }
}
