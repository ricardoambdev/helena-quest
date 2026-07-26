<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Team;
use App\Models\TeamProgress;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Team $team,
        public readonly TeamProgress $progress,
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
        return 'score.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'team_color' => $this->team->color_hex,
            'proof_id' => $this->progress->proof_id,
            'total_score' => $this->progress->total_score,
            'total_time_seconds' => $this->progress->total_time_seconds,
            'stages_completed' => $this->progress->stages_completed,
            'correct_answers' => $this->progress->correct_answers,
            'wrong_answers' => $this->progress->wrong_answers,
            'hints_bought' => $this->progress->hints_bought,
        ];
    }
}
