<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AudioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FinalEnigmaController;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\StageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Sanctum)
|--------------------------------------------------------------------------
*/

// ========== Autenticacao ==========
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('check', [AuthController::class, 'check']);
    });
});

// ========== Etapas ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::get('stages/current', [StageController::class, 'current']);
    Route::post('stages/{stage}/validate-qr', [StageController::class, 'validateQr']);
    Route::post('stages/{stage}/send-photo', [StageController::class, 'sendPhoto']);
    Route::post('stages/{stage}/answer', [StageController::class, 'answer']);
    Route::post('stages/{stage}/unlock', [StageController::class, 'unlock']);
    Route::get('stages/{stage}/hints', [StageController::class, 'hints']);
    Route::post('stages/{stage}/buy-hint/{hint}', [StageController::class, 'buyHint']);
});

// ========== Bonus/Onus ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::post('bonus-onus/scan', [StageController::class, 'scanBonusOnus']);
});

// ========== Audios ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::post('audios', [AudioController::class, 'upload']);
    Route::get('audios', [AudioController::class, 'index']);
});

// ========== Enigma Final ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::get('final-enigma/status', [FinalEnigmaController::class, 'status']);
    Route::post('final-enigma/validate-cofre', [FinalEnigmaController::class, 'validateCofre']);
    Route::post('final-enigma/guess', [FinalEnigmaController::class, 'guess']);
    Route::get('final-enigma/attempts', [FinalEnigmaController::class, 'attempts']);
});

// ========== Publico (Tela) ==========
Route::prefix('public')->group(function () {
    Route::get('competition/{id}', [PublicApiController::class, 'competition']);
    Route::get('teams-location/{competitionId}', [PublicApiController::class, 'teamsLocation']);
    Route::get('ranking/{competitionId}', [PublicApiController::class, 'ranking']);
    Route::get('progress/{competitionId}', [PublicApiController::class, 'progress']);
    Route::get('photos/{competitionId}', [PublicApiController::class, 'photos']);
    Route::get('audios/{competitionId}', [PublicApiController::class, 'audios']);
});
