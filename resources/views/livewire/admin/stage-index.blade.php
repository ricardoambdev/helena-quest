@php
    $allStages = $this->stages;
    $competitions = $this->competitions;
@endphp

<div>
    <header class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Listagem</p>
            <h1 class="font-display font-extrabold text-3xl text-ink">Etapas</h1>
   </div>
        <a href="{{ route('admin.stages.create') }}" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold hover:bg-ember transition-colors duration-200">Nova etapa</a>
</header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 shadow-card">
        <select wire:model.live="competitionFilter" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none w-full">
            <option value="">Todas as competicoes</option>
            @foreach ($competitions as $c)
                <option value="{{ $c->id }}">@php echo $c->name; @endphp</option>
            @endforeach
 </select>
</div>

    <div class="bg-white rounded-card border border-rule shadow-card overflow-hidden">
        @if ($allStages->isEmpty())
            <p class="p-8 text-center text-chalk">Nenhuma etapa encontrada</p>
        @else
            <ul class="stage-ladder p-6 space-y-3">
                @foreach ($allStages as $s)
                    <li class="step">
                        <div class="bg-white rounded-card border border-rule px-5 py-4 shadow-card flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-display font-bold text-ink">@php echo $s->name; @endphp</h3>
                                <p class="text-chalk text-sm">@php echo $s->competition->name; @endphp · @php echo $s->stage_type; @endphp</p>
                               <p class="font-mono text-[10px] text-chalk mt-2 break-all">QR: @php echo $s->qr_code_uuid; @endphp</p>
                      </div>
                            <a href="{{ route('admin.stages.edit', $s->id) }}" class="text-ignite font-display font-semibold">Editar →</a>
                    </div>
                 </li>
                @endforeach
         </ul>
        @endif
 </div>
</div>
