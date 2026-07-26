@php
    $totals = $this->totals;
    $recent = $this->recentCompetitions;
    $rank = $this->liveRanking;
    $cards = [
        ['label' => 'Competições', 'value' => $totals['competitions'], 'hint' => 'incluindo arquivadas'],
        ['label' => 'Em andamento', 'value' => $totals['ongoing'], 'hint' => 'acontecendo agora'],
        ['label' => 'Provas', 'value' => $totals['proofs'], 'hint' => 'em todas as competições'],
        ['label' => 'Equipes ativas', 'value' => $totals['teams_active'] . '/' . $totals['teams_total'], 'hint' => 'status ativo'],
    ];
@endphp

<div class="max-w-6xl">
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Resumo</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Painel geral</h1>
        <p class="text-chalk mt-1">Visão consolidada de todas as competições</p>
    </header>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        @foreach ($cards as $card)
            <div class="bg-white rounded-xl border border-rule px-5 py-5">
                <p class="font-display text-[11px] uppercase tracking-[0.16em] text-chalk">@php echo $card['label']; @endphp</p>
                <p class="font-display font-extrabold text-3xl text-ink mt-1">@php echo $card['value']; @endphp</p>
                <p class="text-xs text-chalk mt-1">@php echo $card['hint']; @endphp</p>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-rule p-6">
            <h2 class="font-display font-bold text-lg text-ink mb-4">Competições recentes</h2>
            @if ($recent->isEmpty())
                <p class="text-chalk text-sm py-6">
                    Nenhuma competição cadastrada.
                    <a href="{{ route('admin.competitions.create') }}" class="text-ignite font-display font-semibold hover:underline">Criar a primeira</a>
                </p>
            @else
                <div class="divide-y divide-rule">
                    @foreach ($recent as $c)
                        @php
                            $css = match ($c->status) {
                                'planning'  => 'bg-chalk/10 text-chalk',
                                'published' => 'bg-flame/20 text-ember',
                                'ongoing'   => 'bg-ignite/15 text-ember',
                                'finished'  => 'bg-ink/10 text-ink',
                                default     => 'bg-rule text-chalk',
                            };
                        @endphp
                        <div class="py-3 flex items-baseline gap-3">
                            <span class="font-mono text-xs text-chalk">@php echo $c->year; @endphp</span>
                            <a href="{{ route('admin.competitions.edit', $c->id) }}" class="flex-1 font-display font-semibold text-ink hover:text-ignite">@php echo $c->name; @endphp</a>
                            <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-pill {{ $css }}">@php echo $c->status; @endphp</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-rule p-6">
            <h2 class="font-display font-bold text-lg text-ink mb-4">Top 5 em pontuação</h2>
            @if ($rank->isEmpty())
                <p class="text-chalk text-sm py-6">Sem pontuação registrada ainda</p>
            @else
                <div class="space-y-1">
                    @foreach ($rank as $i => $row)
                        <div class="flex items-center gap-3 py-2 border-b border-rule last:border-0">
                            <span class="font-display font-extrabold text-2xl text-ignite w-8">@php echo $i + 1; @endphp</span>
                            <span class="w-3 h-3 rounded-full shrink-0" style="background:@php echo $row->color_hex; @endphp"></span>
                            <span class="flex-1 font-display font-semibold text-ink">@php echo $row->name; @endphp</span>
                            <span class="font-mono text-sm text-ink">@php echo $row->total_score; @endphp pts</span>
                            <span class="text-xs text-chalk">@php echo $row->stages_completed; @endphp etapas</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
