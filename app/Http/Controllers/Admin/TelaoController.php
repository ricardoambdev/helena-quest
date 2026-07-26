<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Contracts\View\View;

class TelaoController extends Controller
{
    public function index(): View
    {
        $competitions = Competition::orderByDesc('year')->get();

        return view('admin.telao.index', compact('competitions'));
    }

    public function show(int $competition): View
    {
        $competition = Competition::with(['teams', 'proofs'])->findOrFail($competition);

        return view('admin.telao.show', compact('competition'));
    }
}
