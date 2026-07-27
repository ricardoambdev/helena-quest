<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Equipe</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">@php echo $this->name ?: "Nova equipe"; @endphp</h1>
        <p class="text-chalk mt-2">Cadastre a equipe com usuário, senha e logo</p>
    </header>

    <form wire:submit.prevent="save" class="bg-white rounded-card border border-rule p-6 shadow-card grid grid-cols-1 md:grid-cols-2 gap-5">
        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Competição</span>
            <select wire:model="competition_id" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                <option value="">Selecione</option>
                @foreach ($this->competitionsList as $c)
                    <option value="@php echo $c->id; @endphp">@php echo $c->name; @endphp (@php echo $c->year; @endphp)</option>
                @endforeach
            </select>
            @error("competition_id") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Status</span>
            <select wire:model="status" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                @foreach (["active","blocked","inactive","eliminated"] as $s)
                    <option value="@php echo $s; @endphp">@php echo $s; @endphp</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Nome</span>
            <input type="text" wire:model="name" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            @error("name") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Cor oficial</span>
            <input type="color" wire:model="color_hex" class="h-10 w-20 rounded-card border border-rule">
            @error("color_hex") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Usuário</span>
            <input type="text" wire:model="username" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
            @error("username") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Senha</span>
            <div class="flex gap-2">
                <input type="text" readonly value="{{ $this->generatedPassword }}"
                       class="flex-1 px-3 py-2 rounded-card border border-rule bg-gray-50 outline-none font-mono tracking-widest cursor-pointer"
                       onclick="this.select()" title="Clique para copiar">
                <button type="button" wire:click="regeneratePassword"
                        class="px-3 py-2 rounded-card bg-rule text-ink font-display text-sm font-semibold hover:bg-chalk/20 transition-colors shrink-0">
                    Gerar
                </button>
            </div>
            @if ($this->generatedPassword !== '')
                <span class="text-[11px] text-chalk">Copie a senha — não será exibida novamente após recarregar a página</span>
            @endif
            <input type="hidden" wire:model="password">
            @error("password") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
        </label>

        <label class="flex flex-col gap-1 md:col-span-2">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Logo da equipe</span>
            <input type="file" wire:model="logo" accept="image/png" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none text-sm">
            @error("logo") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
            @if ($this->team?->exists && $this->team->crest_path)
                <div class="mt-2 flex items-center gap-3">
                    <img src="@php echo asset('storage/' . $this->team->crest_path); @endphp" alt="Logo" class="w-16 h-16 rounded-card object-cover border border-rule">
                    <button type="button" wire:click="removeLogo" class="text-xs text-ember font-display font-semibold hover:underline">Remover logo</button>
                </div>
            @endif
        </label>

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Descrição</span>
            <textarea wire:model="description" rows="3" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none"></textarea>
            @error("description") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
        </label>

        <div class="md:col-span-2 flex items-end justify-end gap-3 pt-2">
            @if ($this->team?->exists)
                @if ($this->status === "active" || $this->status === "blocked")
                    <button type="button" wire:click="@php echo $this->status === "active" ? "block" : "unblock"; @endphp" class="px-4 py-2 rounded-card border border-ember text-ember font-display font-semibold">
                        @php echo $this->status === "active" ? "Bloquear" : "Desbloquear"; @endphp
                    </button>
                @endif
                <button type="button" wire:click="delete" wire:confirm="Excluir (soft) esta equipe?" class="px-4 py-2 rounded-card border border-ink text-ink font-display font-semibold">Excluir</button>
                <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold">Salvar</button>
            @else
                <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold">Criar equipe</button>
            @endif
        </div>
    </form>
</div>
