<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Audio;
use App\Models\Stage;
use App\Models\Team;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamAudioSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Team $team,
        public readonly ?Stage $stage,
        public readonly Audio $audio,
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
        return 'audio.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'team_color' => $this->team->color_hex,
            'stage_id' => $this->stage?->id,
            'audio_url' => \Illuminate\Support\Facades\Storage::url($this->audio->audio_path),
            'duration_seconds' => $this->audio->duration_seconds,
            'sent_at' => $this->audio->sent_at?->toIso8601String(),
        ];
    }
}
