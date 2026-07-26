<div>
    <header class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Relatórios</p>
            <h1 class="font-display font-extrabold text-3xl text-ink">Por Equipe</h1>
        </div>
    </header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 shadow-card">
        <select wire:model.live="teamId" class="w-full px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            <option value="">Selecione uma equipe</option>
            @foreach ($this->teams as $t)
                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->competition?->name ?? '—' }})</option>
            @endforeach
        </select>
    </div>

    @if ($team)
        <div class="bg-white rounded-card border border-rule shadow-card overflow-hidden">
            <div class="p-4 border-b border-rule flex items-center justify-between">
                <div>
                    <h2 class="font-display font-bold text-lg" style="color:{{ $team->color_hex }}">{{ $team->name }}</h2>
                    <p class="text-chalk text-sm">{{ $team->competition?->name ?? '—' }} &middot; {{ $team->stageProgress->where('status', 'completed')->count() }} etapas concluídas</p>
                </div>
                <button wire:click="exportCsv" class="px-4 py-2 rounded-card border border-ignite text-ignite font-display font-semibold hover:bg-ignite hover:text-paper transition-colors duration-200 text-sm">
                    Exportar CSV
                </button>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-rule font-display text-[10px] uppercase tracking-wider text-chalk">
                        <th class="text-left py-3 px-4">Prova</th>
                        <th class="text-left py-3 px-4">Etapa</th>
                        <th class="text-center py-3 px-4">Status</th>
                        <th class="text-right py-3 px-4">Pontos</th>
                        <th class="text-right py-3 px-4">Tempo</th>
                        <th class="text-center py-3 px-4">Tentativas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rule">
                    @forelse ($team->stageProgress()->with('stage.proof')->orderBy('stage_id')->get() as $sp)
                        @php
                            $statusClass = match($sp->status) {
                                'completed' => 'bg-green-100 text-green-800',
                                'active', 'photo_sent', 'answered_wrong' => 'bg-flame/20 text-ember',
                                default => 'bg-white/10 text-chalk',
                            };
                            $minutes = intdiv($sp->time_spent_seconds ?? 0, 60);
                            $secs = ($sp->time_spent_seconds ?? 0) % 60;
                        @endphp
                        <tr class="hover:bg-paper/50 transition-colors">
                            <td class="py-3 px-4 text-chalk">{{ $sp->stage?->proof?->name ?? '—' }}</td>
                            <td class="py-3 px-4 font-display font-semibold">{{ $sp->stage?->name ?? '—' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold uppercase {{ $statusClass }}">{{ $sp->status }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono tabular-nums">{{ $sp->score_earned ?? '—' }}</td>
                            <td class="py-3 px-4 text-right font-mono text-chalk">{{ sprintf('%dm%02ds', $minutes, $secs) }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $sp->attempts_count ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-chalk">Nenhum progresso registrado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <p class="text-chalk text-center py-12">Selecione uma equipe para ver o relatório</p>
    @endif
</div>
