@php
    use Illuminate\Support\Facades\Route;
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

<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-2">Resumo</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Painel geral</h1>
        <p class="text-chalk mt-2 max-w-2xl">Visão consolidada de todas as competições, status das equipes e passos sugeridos pela organização</p>
  </header>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        @foreach ($cards as $card)
            <article class="bg-white rounded-card border border-rule px-5 py-5 shadow-card">
                <p class="font-display text-[11px] uppercase tracking-[0.16em] text-chalk">@php echo $card['label']; @endphp</p>
                <p class="font-display font-extrabold text-4xl text-ink mt-2">@php echo $card['value']; @endphp</p>
                <p class="text-xs text-chalk mt-2">@php echo $card['hint']; @endphp</p>
          </article>
        @endforeach
  </div>

    <section class="grid lg:grid-cols-2 gap-6">
        <article class="bg-white rounded-card border border-rule p-6 shadow-card">
            <h2 class="font-display font-bold text-lg text-ink mb-4">Competições recentes</h2>

            @if ($recent->isEmpty())
                <p class="text-chalk text-sm py-6">
                    Nenhuma competição cadastrada.
                    <a href="{{ route('competitions.create') }}" class="text-ignite font-display font-semibold">Criar a primeira</a>
              </p>
            @else
                <ul class="divide-y divide-rule">
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
                        <li class="py-3 flex items-baseline gap-3">
                            <span class="font-mono text-xs text-chalk">@php echo $c->year; @endphp</span>
                            <a href="{{ route('competitions.edit', $c->id) }}" class="flex-1 font-display font-semibold text-ink hover:text-ignite">@php echo $c->name; @endphp</a>
                            <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-pill {{ $css }}">@php echo $c->status; @endphp</span>
                      </li>
                    @endforeach
              </ul>
            @endif
      </article>

        <article class="bg-white rounded-card border border-rule p-6 shadow-card">
            <h2 class="font-display font-bold text-lg text-ink mb-4">Top 5 em pontuação</h2>

            @if ($rank->isEmpty())
                <p class="text-chalk text-sm py-6">Sem pontuação registrada ainda</p>
            @else
                <ol class="space-y-2">
                    @foreach ($rank as $i => $row)
                        <li class="flex items-center gap-3 py-2 border-b border-rule last:border-0">
                            <span class="font-display font-extrabold text-2xl text-ignite w-8">@php echo $i + 1; @endphp</span>
                            <span class="w-3 h-3 rounded-full shrink-0" style="background:@php echo $row->color_hex; @endphp</span>
                            <span class="flex-1 font-display font-semibold text-ink">@php echo $row->name; @endphp</span>
                            <span class="font-mono text-ink">@php echo $row->total_score; @endphp pts</span>
                            <span class="text-xs text-chalk">@php echo $row->stages_completed; @endphp etapas</span>
                      </li>
                    @endforeach
              </ol>
            @endif
      </article>
  </section>
</div>
