<div>
    <header class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Relatórios</p>
            <h1 class="font-display font-extrabold text-3xl text-ink">Por Prova</h1>
        </div>
    </header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 shadow-card">
        <select wire:model.live="proofId" class="w-full px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            <option value="">Selecione uma prova</option>
            @foreach ($this->proofs as $p)
                <option value="{{ $p->id }}">{{ $p->competition?->name ?? '—' }} &middot; {{ $p->name }}</option>
            @endforeach
        </select>
    </div>

    @if ($proof)
        <div class="bg-white rounded-card border border-rule shadow-card overflow-hidden">
            <div class="p-4 border-b border-rule flex items-center justify-between">
                <div>
                    <h2 class="font-display font-bold text-lg">{{ $proof->name }}</h2>
                    <p class="text-chalk text-sm">{{ $proof->competition?->name ?? '—' }} &middot; {{ $proof->stages->count() }} etapas</p>
                </div>
                <button wire:click="exportCsv" class="px-4 py-2 rounded-card border border-ignite text-ignite font-display font-semibold hover:bg-ignite hover:text-paper transition-colors duration-200 text-sm">
                    Exportar CSV
                </button>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-rule font-display text-[10px] uppercase tracking-wider text-chalk">
                        <th class="text-left py-3 px-4">Etapa</th>
                        <th class="text-center py-3 px-4">Total</th>
                        <th class="text-center py-3 px-4">Completos</th>
                        <th class="text-center py-3 px-4">Ativos</th>
                        <th class="text-center py-3 px-4">% Conclusão</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rule">
                    @forelse ($proof->stages as $stage)
                        @php
                            $total = $stage->teamStageProgress->count();
                            $completed = $stage->teamStageProgress->where('status', 'completed')->count();
                            $active = $stage->teamStageProgress->whereIn('status', ['active', 'photo_sent', 'answered_wrong'])->count();
                            $pct = $total > 0 ? round(($completed / $total) * 100) : 0;
                            $barColor = $pct >= 80 ? 'bg-green-500' : ($pct >= 40 ? 'bg-flame' : 'bg-chalk');
                        @endphp
                        <tr class="hover:bg-paper/50 transition-colors">
                            <td class="py-3 px-4 font-display font-semibold">{{ $stage->name }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $total }}</td>
                            <td class="py-3 px-4 text-center font-mono text-green-700">{{ $completed }}</td>
                            <td class="py-3 px-4 text-center font-mono text-ember">{{ $active }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center gap-2 justify-end">
                                    <div class="w-20 h-2 bg-rule rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $barColor }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="font-mono text-xs">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-8 text-chalk">Nenhuma etapa nesta prova</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <p class="text-chalk text-center py-12">Selecione uma prova para ver o relatório</p>
    @endif
</div>
