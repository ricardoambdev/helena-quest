<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\TeamStageProgress;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoThumbs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public function __construct(
        public readonly TeamStageProgress $progress,
    ) {}

    public function handle(): void
    {
        if (!$this->progress->photo_url) {
            Log::warning('ProcessPhotoThumbs: no photo_url for progress #' . $this->progress->id);
            return;
        }

        // Em ambiente compartilhado sem GD/Imagick, registramos o placeholder
        try {
            $thumbPath = 'public/teams/' . $this->progress->team_id . '/thumbs/';
            Storage::makeDirectory($thumbPath);

            // TODO: substituir por intervenção/imagem ou GD quando disponível
            Log::info('ProcessPhotoThumbs: thumb mark for ' . $this->progress->photo_url);
        } catch (\Throwable $e) {
            Log::error('ProcessPhotoThumbs failed: ' . $e->getMessage());
            $this->fail($e);
        }
    }
}
