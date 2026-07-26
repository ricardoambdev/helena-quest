<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Prova</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">@php echo $this->name ?: "Nova prova"; @endphp</h1>
        <p class="text-chalk mt-2">Vincule a prova a uma competição e adicione etapas depois</p>
  </header>

    <form wire:submit.prevent="save" class="bg-white rounded-card border border-rule p-6 shadow-card grid grid-cols-1 md:grid-cols-2 gap-5">
        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Competição</span>
            <select wire:model="competition_id" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                <option value="">Selecione</option>
                @foreach ($this->competitionsList as $c)
                    <option value="@php echo $c->id; @endphp">@php echo $c->name; @endphp (@php echo $c->year; @endphp)</option>
                @endforeach
          </select>
            @error("competition_id") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
      </label>

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Nome</span>
            <input type="text" wire:model="name" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            @error("name") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
      </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Ordem</span>
            <input type="number" min="1" wire:model="order" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
            @error("order") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
      </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Cor</span>
            <input type="color" wire:model="color_hex" class="h-10 w-20 rounded-card border border-rule">
      </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Status</span>
            <select wire:model="status" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                @foreach (["configuring","active","inactive","finished"] as $s)
                    <option value="@php echo $s; @endphp">@php echo $s; @endphp</option>
                @endforeach
          </select>
      </label>

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Descrição</span>
            <textarea wire:model="description" rows="3" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none"></textarea>
      </label>

        <div class="md:col-span-2 flex items-end justify-end gap-3 pt-2">
            @if ($this->proof?->exists)
                <button type="button" wire:click="delete" wire:confirm="Excluir esta prova?" class="px-4 py-2 rounded-card border border-ember text-ember font-display font-semibold">Excluir</button>
                <a href="@php echo route("admin.stages.create", ["proof_id" => $this->proof->id]); @endphp" class="px-4 py-2 rounded-card border border-flame text-ember font-display font-semibold">Adicionar etapa</a>
                <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold">Salvar</button>
            @else
                <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold">Criar prova</button>
            @endif
      </div>
  </form>

    @if ($this->proof?->exists)
        <section class="mt-10">
            <h2 class="font-display font-bold text-xl mb-4">Etapas desta prova</h2>
            @if ($this->stages->isEmpty())
                <p class="text-chalk italic py-6 text-center">Nenhuma etapa cadastrada ainda</p>
            @else
                <ol class="stage-ladder space-y-3">
                    @foreach ($this->stages as $s)
                        <li class="step">
                            <div class="bg-white rounded-card border border-rule px-5 py-3 shadow-card flex items-center justify-between">
                                <span class="font-display font-semibold">@php echo $s->name; @endphp</span>
                                <a href="@php echo route("admin.stages.edit", $s->id); @endphp" class="text-ignite font-display font-semibold">Editar</a>
                         </div>
                     </li>
                    @endforeach
             </ol>
            @endif
     </section>
    @endif
</div>