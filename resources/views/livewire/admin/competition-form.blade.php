@php
    $stagesColl = $this->stages;
@endphp

<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-2">
            @php echo $this->competition?->exists ? 'Editando' : 'Nova competição'; @endphp
       </p>
        <h1 class="font-display font-extrabold text-3xl text-ink">
            @php echo $this->name ?: 'Detalhes da competição'; @endphp
       </h1>
        <p class="text-chalk mt-2 max-w-2xl">
            Configure a competição antes de adicionar provas e etapas. Publicação exige ao menos uma prova cadastrada.
       </p>
 </header>

    <form wire:submit.prevent="save" class="bg-white rounded-card border border-rule p-6 shadow-card grid grid-cols-1 md:grid-cols-2 gap-5">

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Nome</span>
            <input type="text" wire:model="name" class="px-3 py-2 rounded-card border border-rule focus:border-ignite focus:ring-2 focus:ring-ignite/20 outline-none">
            @error('name') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
     </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Ano</span>
            <input type="number" min="2000" max="2100" wire:model="year" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            @error('year') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
     </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Data oficial</span>
            <input type="date" wire:model="date" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            @error('date') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
     </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Início</span>
            <input type="datetime-local" wire:model="start_time" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            @error('start_time') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
     </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Encerramento previsto</span>
            <input type="datetime-local" wire:model="end_time" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            @error('end_time') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
     </label>

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Descrição</span>
            <textarea wire:model="description" rows="3" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none"></textarea>
            @error('description') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
     </label>

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Regulamento (Markdown)</span>
            <textarea wire:model="rules_markdown" rows="6" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono text-xs"></textarea>
            @error('rules_markdown') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
     </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Status atual</span>
            <select wire:model="status" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                @foreach (['planning','published','ongoing','paused','finished','archived'] as $s)
                    <option value="{{ $s }}">@php echo $s; @endphp</option>
                @endforeach
         </select>
      </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Limite de equipes</span>
            <input type="number" min="1" max="999" wire:model="max_teams" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            <span class="text-[11px] text-chalk">Número máximo de equipes que podem participar. Ajustável apenas aqui.</span>
            @error('max_teams') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
      </label>

        <div class="flex items-end justify-end gap-3">
            @if ($this->competition?->exists)
                <button type="button" wire:click="publish" class="px-4 py-2 rounded-card border border-flame text-ember font-display font-semibold hover:bg-flame/10 transition-colors duration-200">
                    Publicar
             </button>
                @if ($this->competition->status === 'planning')
                    <button type="button" wire:click="startCompetition" wire:confirm="Iniciar a competição agora?" class="px-4 py-2 rounded-card bg-ember text-paper font-display font-semibold hover:bg-ignite transition-colors duration-200">
                        Iniciar agora
                 </button>
                @elseif ($this->competition->status === 'ongoing')
                    <button type="button" wire:click="pause" class="px-4 py-2 rounded-card bg-ink text-paper font-display font-semibold hover:bg-ink/85 transition-colors duration-200">
                        Pausar
                 </button>
                @endif
                @if (in_array($this->competition->status, ['ongoing', 'paused'], true))
                    <button type="button" wire:click="finish" wire:confirm="Encerrar definitivamente?" class="px-4 py-2 rounded-card border border-ink text-ink font-display font-semibold hover:bg-ink hover:text-paper transition-colors duration-200">
                        Encerrar
                 </button>
                @endif
                <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold hover:bg-ember transition-colors duration-200">
                    Salvar alterações
             </button>
            @else
                <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold hover:bg-ember transition-colors duration-200">
                    Criar competição
             </button>
            @endif
      </div>
 </form>

    @if ($this->competition?->exists)
        <section class="mt-10">
            <header class="flex items-baseline justify-between mb-4">
                <div>
                    <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Etapas</p>
                    <h2 class="font-display font-bold text-2xl text-ink">@php echo $stagesColl->count(); @endphp etapa(s) nesta competicao</h2>
             </div>
                <a href="{{ route('admin.stages.create', ['competition_id' => $this->competition->id]) }}" class="px-4 py-2 rounded-card border border-ink text-ink font-display font-semibold hover:bg-ink hover:text-paper transition-colors duration-200">
                    Adicionar etapa
             </a>
         </header>

            @if ($stagesColl->isEmpty())
                <p class="text-chalk italic py-8 text-center">Nenhuma etapa cadastrada. Adicione ao menos uma para publicar a competicao</p>
            @else
                <ul class="stage-ladder space-y-3">
                    @foreach ($stagesColl as $s)
                        <li class="step bg-white rounded-card border border-rule px-5 py-4 shadow-card">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-display font-bold text-ink">@php echo $s->name; @endphp</h3>
                                    <p class="text-chalk text-sm">@php echo $s->stage_type ?? '—'; @endphp</p>
                                    <p class="text-[11px] uppercase tracking-wider text-chalk mt-2">Ordem @php echo $s->order; @endphp</p>
                             </div>
                                <a href="{{ route('admin.stages.edit', $s->id) }}" class="text-ignite font-display font-semibold">Editar →</a>
                         </div>
                      </li>
                    @endforeach
              </ul>
            @endif
     </section>
    @endif
</div>
