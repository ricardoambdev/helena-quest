<?php

declare(strict_types=1);

use App\Models\Team;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Antenas de Broadcasting (Reverb)
|--------------------------------------------------------------------------
|
| Os canais `team.{id}` são PRIVATE — só a equipe proprietária (autenticada
| via Sanctum no app mobile) recebe as notificações privadas.
|
| Os canais `competition.{id}` são PUBLIC — qualquer cliente (incluindo o
| telão sem login) pode escutar o status da competição, ranking, fotos
| e áudios em tempo real.
|
*/

Broadcast::channel('team.{teamId}', function ($user, int $teamId): bool {
    if ($user instanceof Team) {
        return $user->id === $teamId;
    }
    return false;
});

Broadcast::channel('competition.{competitionId}', function ($user, int $competitionId): bool {
    return true;
});
