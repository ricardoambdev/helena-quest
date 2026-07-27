<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Equipe</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">{{ $this->name ?: 'Nova equipe' }}</h1>
        <p class="text-chalk mt-2">Cadastre a equipe com usuário, senha e logo</p>
    </header>

    <form wire:submit.prevent="save" class="bg-white rounded-card border border-rule p-6 shadow-card grid grid-cols-1 md:grid-cols-2 gap-5">
        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Competição</span>
            <select wire:model="competition_id" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                <option value="">Selecione</option>
                @foreach ($this->competitionsList as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->year }})</option>
                @endforeach
            </select>
            @error('competition_id') <span class="text-ember text-xs">{{ $message }}</span> @enderror
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Status</span>
            <select wire:model="status" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                @foreach (['active','blocked','inactive','eliminated'] as $s)
                    <option value="{{ $s }}">{{ $s }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Nome</span>
            <input type="text" wire:model="name" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            @error('name') <span class="text-ember text-xs">{{ $message }}</span> @enderror
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Cor oficial</span>
            <input type="color" wire:model="color_hex" class="h-10 w-20 rounded-card border border-rule">
            @error('color_hex') <span class="text-ember text-xs">{{ $message }}</span> @enderror
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Usuário</span>
            <input type="text" wire:model="username" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
            @error('username') <span class="text-ember text-xs">{{ $message }}</span> @enderror
        </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Senha</span>
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input :type="showPassword ? 'text' : 'password'"
                           wire:model="password"
                           class="w-full px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono tracking-widest pr-10"
                           placeholder="Senha da equipe">
                    <button type="button" wire:click="togglePassword"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-chalk hover:text-ink transition-colors p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <template x-if="!showPassword">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </template>
                            <template x-if="showPassword">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </template>
                        </svg>
                    </button>
                </div>
            </div>
            @error('password') <span class="text-ember text-xs">{{ $message }}</span> @enderror
            @if ($this->team?->exists)
                <span class="text-[11px] text-chalk">Altere a senha se necessário e salve</span>
            @endif
        </label>

        <label class="flex flex-col gap-1 md:col-span-2">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Logo da equipe</span>
            <input type="file" wire:model="logo" accept="image/png" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none text-sm">
            @error('logo') <span class="text-ember text-xs">{{ $message }}</span> @enderror
            @if ($this->team?->exists && $this->team->crest_path)
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ asset('storage/' . $this->team->crest_path) }}" alt="Logo" class="w-16 h-16 rounded-card object-cover border border-rule">
                    <button type="button" wire:click="removeLogo" class="text-xs text-ember font-display font-semibold hover:underline">Remover logo</button>
                </div>
            @endif
        </label>

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Descrição</span>
            <textarea wire:model="description" rows="3" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none"></textarea>
            @error('description') <span class="text-ember text-xs">{{ $message }}</span> @enderror
        </label>

        <div class="md:col-span-2 flex items-end justify-end gap-3 pt-2">
            @if ($this->team?->exists)
                @if ($this->status === 'active' || $this->status === 'blocked')
                    <button type="button" wire:click="{{ $this->status === 'active' ? 'block' : 'unblock' }}" class="px-4 py-2 rounded-card border border-ember text-ember font-display font-semibold">
                        {{ $this->status === 'active' ? 'Bloquear' : 'Desbloquear' }}
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
