<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Team;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Team $team,
        public readonly float $latitude,
        public readonly float $longitude,
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
        return 'location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'team_color' => $this->team->color_hex,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
