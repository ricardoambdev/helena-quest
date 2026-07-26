<div>
    <header class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Relatórios</p>
            <h1 class="font-display font-extrabold text-3xl text-ink">Por Competição</h1>
        </div>
    </header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 shadow-card">
        <select wire:model.live="competitionId" class="w-full px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            <option value="">Selecione uma competição</option>
            @foreach ($this->competitions as $c)
                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->date?->format('d/m/Y') ?? 'sem data' }})</option>
            @endforeach
        </select>
    </div>

    @if ($data)
        <div class="bg-white rounded-card border border-rule shadow-card overflow-hidden">
            <div class="p-4 border-b border-rule flex items-center justify-between">
                <div>
                    <h2 class="font-display font-bold text-lg">{{ $data['competition_name'] }}</h2>
                    <p class="text-chalk text-sm">{{ $data['total_teams'] }} equipes &middot; {{ $data['total_stages'] }} etapas</p>
                </div>
                <button wire:click="exportCsv" class="px-4 py-2 rounded-card border border-ignite text-ignite font-display font-semibold hover:bg-ignite hover:text-paper transition-colors duration-200 text-sm">
                    Exportar CSV
                </button>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-rule font-display text-[10px] uppercase tracking-wider text-chalk">
                        <th class="text-left py-3 px-4">#</th>
                        <th class="text-left py-3 px-4">Equipe</th>
                        <th class="text-center py-3 px-4">Etapas</th>
                        <th class="text-right py-3 px-4">Pontuação</th>
                        <th class="text-center py-3 px-4">Fotos</th>
                        <th class="text-center py-3 px-4">Áudios</th>
                        <th class="text-center py-3 px-4">Dicas</th>
                        <th class="text-right py-3 px-4">Tempo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rule">
                    @foreach ($data['teams'] as $i => $row)
                        @php $minutes = intdiv($row['time_seconds'], 60); $secs = $row['time_seconds'] % 60; @endphp
                        <tr class="hover:bg-paper/50 transition-colors">
                            <td class="py-3 px-4 font-mono text-chalk">{{ $i + 1 }}</td>
                            <td class="py-3 px-4 font-display font-semibold" style="border-left:3px solid {{ $row['color'] }}">{{ $row['name'] }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $row['stages_completed'] }}/{{ $data['total_stages'] }}</td>
                            <td class="py-3 px-4 text-right font-display font-bold text-ignite tabular-nums">{{ number_format($row['total_score'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $row['photos'] }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $row['audios'] }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $row['hints'] }}</td>
                            <td class="py-3 px-4 text-right font-mono text-chalk">{{ sprintf('%dm%02ds', $minutes, $secs) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-chalk text-center py-12">Selecione uma competição para ver o relatório</p>
    @endif
</div>
