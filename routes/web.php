<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\TelaoController;
use App\Livewire\Admin\CompetitionForm;
use App\Livewire\Telao;
use App\Livewire\Admin\CompetitionIndex;
use App\Livewire\Admin\CompetitionReport;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\FinalEnigmaForm;
use App\Livewire\Admin\GameLogsIndex;
use App\Livewire\Admin\LogsIndex;
use App\Livewire\Admin\ProofForm;
use App\Livewire\Admin\ProofIndex;
use App\Livewire\Admin\ProofReport;
use App\Livewire\Admin\RankingLive;
use App\Livewire\Admin\StageForm;
use App\Livewire\Admin\StageIndex;
use App\Livewire\Admin\TeamForm;
use App\Livewire\Admin\TeamIndex;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\TeamReport;
use App\Livewire\Admin\TeamMonitor;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Painel Administrativo (Livewire)
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/admin')->name('home');

// ============ Telão (Público) ============
Route::get('/telao', [TelaoController::class, 'index'])->name('telao.index');
Route::get('/telao/{competition}', Telao::class)->name('telao.show');

// ============ Auth Admin ============
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', Dashboard::class)->name('dashboard');

        Route::get('/competitions', CompetitionIndex::class)->name('competitions.index');
        Route::get('/competitions/create', fn (\Illuminate\Http\Request $r) => redirect()->route('competitions.edit', ['competition' => 'new']))
            ->name('competitions.create');
        Route::get('/competitions/{competition}/edit', CompetitionForm::class)->name('competitions.edit');

        Route::get('/proofs', ProofIndex::class)->name('proofs.index');
        Route::get('/proofs/create', fn (\Illuminate\Http\Request $r) => redirect()->route('proofs.edit', ['proof' => 'new', 'competition_id' => $r->query('competition_id')]))
            ->name('proofs.create');
        Route::get('/proofs/{proof}/edit', ProofForm::class)->name('proofs.edit');

        Route::get('/stages', StageIndex::class)->name('stages.index');
        Route::get('/stages/create', fn (\Illuminate\Http\Request $r) => redirect()->route('stages.edit', ['stage' => 'new', 'proof_id' => $r->query('proof_id')]))
            ->name('stages.create');
        Route::get('/stages/{stage}/edit', StageForm::class)->name('stages.edit');

        Route::get('/teams', TeamIndex::class)->name('teams.index');
        Route::get('/teams/create', TeamForm::class)->name('teams.create');
        Route::get('/teams/{team}/edit', TeamForm::class)->name('teams.edit');

        Route::get('/final-enigma', FinalEnigmaForm::class)->name('final-enigma.index');
        Route::get('/final-enigma/{enigma}/edit', FinalEnigmaForm::class)->name('final-enigma.edit');

        Route::get('/ranking', RankingLive::class)->name('ranking');
        Route::get('/monitor', TeamMonitor::class)->name('monitor');

        Route::get('/logs', LogsIndex::class)->name('logs.index');
        Route::get('/game-logs', GameLogsIndex::class)->name('game-logs.index');

        Route::get('/reports/competition', CompetitionReport::class)->name('reports.competition');
        Route::get('/reports/team', TeamReport::class)->name('reports.team');
        Route::get('/reports/proof', ProofReport::class)->name('reports.proof');

        Route::get('/settings', \App\Livewire\Admin\Settings::class)->name('settings');
    });
});
