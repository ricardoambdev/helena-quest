@php
    $stageCollection = $this->stages;
@endphp

<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Etapas</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">
            @php echo $this->name ?: ($this->proof()?->name ?? 'Nova etapa'); @endphp
    </h1>
        <p class="text-chalk mt-2">Defina o nome, narrativa, geolocalização e resposta da etapa</p>
</header>

    <div class="grid lg:grid-cols-[1fr_360px] gap-6">
        <form wire:submit.prevent="save" class="bg-white rounded-card border border-rule p-6 shadow-card grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="md:col-span-2 flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Nome</span>
                <input type="text" wire:model="name" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                @error('name') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
       </label>

            <label class="flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Ordem</span>
                <input type="number" min="1" wire:model="order" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
       </label>

            <label class="flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Raio GPS (m)</span>
                <input type="number" min="5" max="200" wire:model="radius" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
       </label>

            <label class="flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Latitude</span>
                <input type="number" step="0.00000001" wire:model="latitude" placeholder="-23.550520" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
           </label>

            <label class="flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Longitude</span>
                <input type="number" step="0.00000001" wire:model="longitude" placeholder="-46.633309" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
           </label>

            <label class="md:col-span-2 flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Narrativa (TTS automático no app)</span>
                <textarea wire:model="narrative_text" rows="5" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none</textarea>
                @error('narrative_text') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
       </label>

            <label class="md:col-span-2 flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Dica da próxima etapa</span>
                <textarea wire:model="next_stage_hint" rows="2" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none</textarea>
       </label>

            <label class="flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Resposta correta (4–8 dígitos)</span>
                <input type="text" maxlength="8" pattern="\d{4,8}" wire:model="correct_answer" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono tracking-wider">
                @error('correct_answer') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
           </label>

            <label class="flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Número secreto (4–8 dígitos)</span>
                <input type="text" maxlength="8" pattern="\d{4,8}" wire:model="secret_number" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono tracking-wider">
                @error('secret_number') <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
           </label>

            <label class="flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Pontuação base</span>
                <input type="number" min="0" wire:model="score" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
           </label>

            <label class="flex flex-col gap-1">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Penalidade por tentativa extra</span>
                <input type="number" min="0" wire:model="penalty" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
           </label>

            <div class="md:col-span-2 flex items-end justify-end gap-3 pt-2">
                @if ($this->stage?->exists)
                    <button type="button" wire:click="delete" wire:confirm="Excluir esta etapa?" class="px-4 py-2 rounded-card border border-ember text-ember font-display font-semibold hover:bg-ember hover:text-paper transition-colors duration-200">Excluir</button>
                    <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold hover:bg-ember transition-colors duration-200">Salvar etapa</button>
                @else
                    <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold hover:bg-ember transition-colors duration-200">Criar etapa</button>
                @endif
           </div>
       </form>

        <aside class="space-y-4">
            @if ($this->stage?->exists)
                <div class="bg-white rounded-card border border-rule p-5 shadow-card">
                    <p class="font-display text-[11px] uppercase tracking-[0.16em] text-chalk mb-2">QR Code desta etapa</p>
                    <p class="font-mono text-[10px] text-ink break-all bg-paper p-3 rounded-card border border-rule">@php echo $this->stage->qr_code_uuid; @endphp</p>
                    <button type="button" wire:click="regenerateQr" wire:confirm="Gerar novo QR Code? O anterior será invalidado." class="mt-3 w-full px-3 py-2 rounded-card border border-flame text-ember font-display font-semibold hover:bg-flame/10 transition-colors duration-200">
                        Regenerar QR Code
                   </button>
               </div>
            @endif

            @if ($this->proof())
                <div class="bg-white rounded-card border border-rule p-5 shadow-card">
                    <p class="font-display text-[11px] uppercase tracking-[0.16em] text-chalk mb-3">Etapas desta prova</p>
                    @if ($stageCollection->isEmpty())
                        <p class="text-chalk italic text-sm">Nenhuma etapa cadastrada</p>
                    @else
                        <ol class="space-y-1 text-sm">
                            @foreach ($stageCollection as $s)
                                <li class="flex items-baseline gap-2 {{ $s->id === $this->stage?->id ? 'text-ember font-display font-semibold' : 'text-ink' }}">
                                    <span class="font-mono text-xs text-chalk w-8">@php echo str_pad((string) $s->order, 2, '0', STR_PAD_LEFT); @endphp</span>
                                    <a href="{{ route('admin.stages.edit', $s->id) }}" class="hover:text-ignite">@php echo $s->name; @endphp</a>
                               </li>
                            @endforeach
                       </ol>
                    @endif
               </div>
            @endif
       </aside>
        {{-- Dicas --}}
        @if ($this->stage?->exists)
            <div class="md:col-span-2 border-t border-rule pt-4 mt-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-display text-xs uppercase tracking-wider text-chalk font-semibold">Dicas Extras</h2>
                    <button type="button" wire:click="addHint" class="px-3 py-1 rounded-card bg-ignite text-paper font-display font-bold text-xs hover:bg-ember transition-colors duration-200">+ Adicionar dica</button>
                </div>

                @if (empty($this->hintsData))
                    <p class="text-chalk italic text-sm">Nenhuma dica cadastrada</p>
                @else
                    <div class="space-y-3">
                        @foreach ($this->hintsData as $idx => $hint)
                            <div class="flex items-start gap-3 bg-paper rounded-card border border-rule p-3" wire:key="hint-{{ $idx }}">
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-[1fr_100px] gap-2">
                                    <label class="flex flex-col gap-1">
                                        <span class="font-display text-[10px] uppercase tracking-wider text-chalk">Texto da dica</span>
                                        <textarea wire:model="hintsData.{{ $idx }}.hint_text" rows="2" class="px-2 py-1 rounded-card border border-rule focus:border-ignite outline-none text-sm"></textarea>
                                    </label>
                                    <label class="flex flex-col gap-1">
                                        <span class="font-display text-[10px] uppercase tracking-wider text-chalk">Preço (pts)</span>
                                        <input type="number" min="0" wire:model="hintsData.{{ $idx }}.price" class="px-2 py-1 rounded-card border border-rule focus:border-ignite outline-none font-mono text-sm">
                                    </label>
                                </div>
                                <button type="button" wire:click="removeHint({{ $idx }})" wire:confirm="Remover esta dica?" class="text-ember hover:text-ember/80 mt-1 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <p class="md:col-span-2 text-chalk italic text-sm border-t border-rule pt-4 mt-4">Salve a etapa primeiro para gerenciar as dicas extras.</p>
        @endif
    </div>
</div>
