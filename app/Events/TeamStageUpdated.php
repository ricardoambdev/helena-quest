<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Stage;
use App\Models\Team;
use App\Models\TeamStageProgress;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamStageUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Team $team,
        public readonly Stage $stage,
        public readonly TeamStageProgress $progress,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("team.{$this->team->id}"),
            new Channel("competition.{$this->team->competition_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stage.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'team_color' => $this->team->color_hex,
            'stage_id' => $this->stage->id,
            'stage_name' => $this->stage->name,
            'status' => $this->progress->status,
            'score_earned' => $this->progress->score_earned,
            'attempts_count' => $this->progress->attempts_count,
            'completed_at' => $this->progress->completed_at?->toIso8601String(),
        ];
    }
}
