<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Ao vivo</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Ranking</h1>
        <p class="text-chalk mt-2">Pontuacoes consolidadas em tempo real</p>
</header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 shadow-card">
        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Competicao</span>
            <select wire:model.live="competitionId" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                <option value="">Selecione</option>
                @foreach ($this->competitionsList as $c)
                    <option value="@php echo $c->id; @endphp">@php echo $c->name; @endphp (@php echo $c->year; @endphp</option>
                @endforeach
        </select>
    </label>
</div>

    @if (!$this->competitionId)
        <p class="text-chalk text-sm py-12 text-center">Selecione uma competicao para ver o ranking</p>
    @elseif ($this->ranking->isEmpty())
        <p class="text-chalk text-sm py-12 text-center">Nenhuma equipe pontuou ainda nesta competicao</p>
    @else
        <ol class="space-y-2">
            @foreach ($this->ranking as $i => $row)
                <li class="bg-white rounded-card border border-rule shadow-card px-5 py-4 flex items-center gap-4">
                    <span class="font-display font-extrabold text-3xl text-ignite w-12 text-center">@php echo str_pad((string)($i + 1), 2, "0", STR_PAD_LEFT); @endphp</span>
                    <span class="w-4 h-4 rounded-full shrink-0" style="background:@php echo $row->color_hex; @endphp</span>
                    <span class="flex-1 font-display font-bold text-ink text-lg">@php echo $row->name; @endphp</span>
                    <div class="text-right">
                        <p class="font-mono font-bold text-2xl text-ember">@php echo $row->total_score; @endphp <span class="text-sm text-chalk">pts</span</p>
                        <p class="text-xs text-chalk">@php echo $row->stages_completed; @endphp etapas · @php echo $row->correct; @endphp corretas · @php echo $row->wrong; @endphp erros</p>
                </div>
             </li>
            @endforeach
     </ol>
    @endif
</div>