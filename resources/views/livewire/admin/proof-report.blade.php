<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Relatorios</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Progresso por Etapa</h1>
</header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 shadow-card">
        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Competicao</span>
            <select wire:model.live="competitionId" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                <option value="">Selecione uma competicao</option>
                @foreach ($this->competitions as $c)
                    <option value="{{ $c->id }}">@php echo $c->name; @endphp</option>
                @endforeach
            </select>
        </label>
</div>

    @if ($this->competitionId)
        <div class="bg-white rounded-card border border-rule p-6 shadow-card mb-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display font-bold text-lg">@php echo $this->competition?->name ?? '—'; @endphp</h2>
                <button wire:click="exportCsv" class="px-4 py-2 rounded-card bg-ink text-paper font-display font-semibold text-sm hover:bg-ink/80 transition-colors duration-200">
                    Exportar CSV
                </button>
            </div>

            @if ($stages->isEmpty())
                <p class="text-chalk italic py-8 text-center">Nenhuma etapa cadastrada</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-rule">
                            <tr>
                                <th class="text-left py-3 px-4">Etapa</th>
                                <th class="text-left py-3 px-4">Tipo</th>
                                <th class="text-center py-3 px-4">Total</th>
                                <th class="text-center py-3 px-4">Completos</th>
                                <th class="text-center py-3 px-4">Ativos</th>
                                <th class="text-center py-3 px-4">% Conclusao</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule">
                            @foreach ($stages as $stage)
                                @php
                                    $total = $stage->teamStageProgress->count();
                                    $completed = $stage->teamStageProgress->where('status', 'completed')->count();
                                    $active = $stage->teamStageProgress->whereIn('status', ['active', 'photo_sent', 'answered_wrong'])->count();
                                    $pct = $total > 0 ? round(($completed / $total) * 100) : 0;
                                @endphp
                                <tr>
                                    <td class="py-3 px-4 font-display font-semibold">@php echo $stage->name; @endphp</td>
                                    <td class="py-3 px-4 text-chalk">@php echo $stage->stage_type; @endphp</td>
                                    <td class="py-3 px-4 text-center">@php echo $total; @endphp</td>
                                    <td class="py-3 px-4 text-center text-green-600">@php echo $completed; @endphp</td>
                                    <td class="py-3 px-4 text-center text-ember">@php echo $active; @endphp</td>
                                    <td class="py-3 px-4 text-center font-mono font-semibold">@php echo $pct; @endphp%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
