<div>
    <header class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Auditoria</p>
            <h1 class="font-display font-extrabold text-3xl text-ink">Logs do Jogo</h1>
        </div>
        <button wire:click="exportCsv" class="px-4 py-2 rounded-card border border-ignite text-ignite font-display font-semibold hover:bg-ignite hover:text-paper transition-colors duration-200 text-sm">
            Exportar CSV
        </button>
    </header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 grid grid-cols-1 md:grid-cols-3 gap-3 shadow-card">
        <select wire:model.live="teamFilter" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none text-sm">
            <option value="">Todas as equipes</option>
            @foreach ($this->teams as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="actionFilter" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none text-sm">
            <option value="">Todas as ações</option>
            @foreach ($this->actions as $a)
                <option value="{{ $a }}">{{ $a }}</option>
            @endforeach
        </select>
        <div></div>
    </div>

    <div class="bg-white rounded-card border border-rule shadow-card overflow-x-auto">
        @if ($logs->isEmpty())
            <p class="p-8 text-center text-chalk">Nenhum registro encontrado</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-rule font-display text-[10px] uppercase tracking-wider text-chalk">
                        <th class="text-left py-3 px-4">Data</th>
                        <th class="text-left py-3 px-4">Equipe</th>
                        <th class="text-left py-3 px-4">Ação</th>
                        <th class="text-left py-3 px-4">Contexto</th>
                        <th class="text-left py-3 px-4">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rule">
                    @foreach ($logs as $log)
                        <tr class="hover:bg-paper/50 transition-colors">
                            <td class="py-3 px-4 font-mono text-[11px] text-chalk whitespace-nowrap">{{ $log->created_at?->format('d/m H:i:s') }}</td>
                            <td class="py-3 px-4 font-display font-semibold">{{ $log->team?->name ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold uppercase tracking-wider
                                    @php
                                        $a = $log->action;
                                        echo match(true) {
                                            str_contains($a, 'correct') || str_contains($a, 'solved') => 'bg-green-100 text-green-800',
                                            str_contains($a, 'failed') || str_contains($a, 'wrong') || str_contains($a, 'out_of_range') => 'bg-red-100 text-red-800',
                                            default => 'bg-ignite/10 text-ignite',
                                        };
                                    @endphp
                                ">{{ $log->action }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-[10px] text-chalk max-w-[300px] truncate" title="{{ json_encode($log->context ?? [], JSON_UNESCAPED_UNICODE) }}">
                                @if ($log->context)
                                    @php
                                        $ctx = $log->context;
                                        $parts = [];
                                        foreach (['stage_name', 'letter', 'attempts', 'distance', 'hint_price', 'attempt_number'] as $key) {
                                            if (isset($ctx[$key])) $parts[] = "$key: {$ctx[$key]}";
                                        }
                                        echo implode(' | ', $parts);
                                    @endphp
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono text-[10px] text-chalk">{{ $log->ip ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 border-t border-rule">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
