<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Monitor</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Acompanhamento de equipes</h1>
</header>

    <div class="bg-white rounded-card border border-rule p-4 mb-6 shadow-card">
        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Filtro por competicao</span>
            <select wire:model.live="competitionFilter" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                <option value="">Todas</option>
                <option value="1">Demo 2026</option>
          </select>
    </label>
 </div>

    <div class="grid lg:grid-cols-[280px_1fr] gap-6">
        <aside class="bg-white rounded-card border border-rule p-4 shadow-card max-h-[70vh] overflow-y-auto">
            <h2 class="font-display font-bold mb-3">Equipes</h2>
            @php $teamsList = $this->teams; @endphp
            @forelse ($teamsList as $t)
                <button type="button" wire:click="$set('teamId', {{ $t->id }})" class="w-full text-left px-3 py-2 rounded-card flex items-center gap-2 mb-1 hover:bg-paper">
                    <span class="w-3 h-3 rounded-full" style="background:{{ $t->color_hex }}</span>
                    <span class="flex-1 font-display font-semibold">{{ $t->name</span>
                    <span class="text-[10px] uppercase text-chalk">{{ $t->status</span>
         </button>
            @empty
                <p class="text-chalk text-sm italic py-4">Nenhuma equipe cadastrada</p>
            @endforelse
      </aside>

        <main>
            @php $sel = $this->selectedTeam; @endphp
            @if ($sel)
                @php $progress = $sel->progress->first(); @endphp
                <article class="bg-white rounded-card border border-rule p-6 shadow-card">
                    <header class="flex items-baseline justify-between mb-4">
                        <div>
                            <h2 class="font-display font-extrabold text-2xl">{{ $sel->name</h2>
                            <p class="text-chalk">{{ $sel->competition?->name</p>
                    </div>
                        <span class="text-[11px] uppercase px-2 py-1 rounded-pill" style="background:{{ $sel->color_hex }}33; color:{{ $sel->color_hex }}">{{ $sel->status</span>
               </header>

                    @if ($progress)
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-paper rounded-card p-4 text-center">
                                <p class="font-display font-extrabold text-3xl text-ember">{{ $progress->total_score</p>
                                <p class="text-xs text-chalk uppercase tracking-wider">pontos</p>
                     </div>
                            <div class="bg-paper rounded-card p-4 text-center">
                                <p class="font-display font-extrabold text-3xl text-ember">{{ $progress->stages_completed</p>
                                <p class="text-xs text-chalk uppercase tracking-wider">etapas</p>
                     </div>
                            <div class="bg-paper rounded-card p-4 text-center">
                                <p class="font-display font-extrabold text-3xl text-ember">
                                    @php echo str_pad((string)intdiv((int)$progress->total_time_seconds, 60), 2, "0", STR_PAD_LEFT) . ":" . str_pad((string)((int)$progress->total_time_seconds % 60), 2, "0", STR_PAD_LEFT); @endphp
                              </p>
                                <p class="text-xs text-chalk uppercase tracking-wider">tempo</p>
                     </div>
                   </div>
                    @endif

                    <h3 class="font-display font-bold mb-2">Etapas</h3>
                    @if ($sel->stageProgress->isEmpty())
                        <p class="text-chalk italic">Nenhuma etapa acessada</p>
                    @else
                        <ol class="space-y-2">
                            @foreach ($sel->stageProgress as $p)
                                <li class="flex items-center gap-3 py-2 border-b border-rule">
                                    <span class="font-display font-bold w-8">{{ $p->stage?->order ?? "-"</span>
                                    <span class="flex-1">{{ $p->stage?->name ?? "(etapa removida)"</span>
                                    <span class="text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-pill bg-paper text-chalk">{{ $p->status</span>
                                    <span class="font-mono text-xs text-chalk">{{ $p->score_earned }} pts</span>
                        </li>
                            @endforeach
                   </ol>
                    @endif
            </article>
            @else
                <p class="text-chalk italic py-12 text-center">Selecione uma equipe ao lado</p>
            @endif
    </main>
 </div>
</div>