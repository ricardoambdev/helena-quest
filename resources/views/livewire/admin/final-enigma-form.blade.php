<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Enigma Final</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Palavra + Cofres QR</h1>
        <p class="text-chalk mt-2">Defina a palavra final e distribua cofres com QR Codes pela escola</p>
 </header>

    <form wire:submit.prevent="save" class="bg-white rounded-card border border-rule p-6 shadow-card grid grid-cols-1 md:grid-cols-2 gap-5">
        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Competicao</span>
            <select wire:model="competition_id" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                <option value="">Selecione</option>
                @foreach ($this->competitionsList as $c)
                    <option value="@php echo $c->id; @endphp">@php echo $c->name; @endphp (@php echo $c->year; @endphp)</option>
                @endforeach
         </select>
            @error("competition_id") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
     </label>

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Nome da etapa</span>
            <input type="text" wire:model="name" placeholder="Enigma Final" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            @error("name") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
    </label>

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Narrativa</span>
            <textarea wire:model="narrative_text" rows="4" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none"></textarea>
    </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Palavra final</span>
            <input type="text" maxlength="50" wire:model="word" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono uppercase">
            @error("word") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
   </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Pontuacao por acertar</span>
            <input type="number" min="0" wire:model="word_score" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
   </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Max tentativas</span>
            <input type="number" min="1" max="20" wire:model="max_attempts" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
   </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Penalidade por erro</span>
            <input type="number" min="0" wire:model="wrong_word_penalty" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
   </label>

        <div class="md:col-span-2 pt-2 border-t border-rule">
            <header class="flex items-center justify-between mb-3">
                <h3 class="font-display font-bold">Cofres (QR Code para escanear)</h3>
                <button type="button" wire:click="addCofre" class="text-ignite font-display font-semibold">+ Adicionar cofre</button>
        </header>
            @if (empty($cofres))
                <p class="text-chalk italic text-sm">Nenhum cofre cadastrado</p>
            @else
                <ol class="space-y-3">
                    @foreach ($cofres as $i => $c)
                        <li class="bg-paper rounded-card p-3 border border-rule" wire:key="cofre-{{ $i }}">
                            <div class="flex items-start gap-3">
                                <span class="font-display font-bold text-ember mt-2 w-8 text-center shrink-0">@php echo $loop->iteration; @endphp</span>
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <label class="flex flex-col gap-1">
                                        <span class="font-display text-[10px] uppercase tracking-wider text-chalk">Nome do cofre</span>
                                        <input type="text" wire:model="cofres.{{ $i }}.name" placeholder="Cofre da biblioteca" class="px-2 py-1 rounded-card border border-rule focus:border-ignite outline-none text-sm">
                                    </label>
                                    <label class="flex flex-col gap-1">
                                        <span class="font-display text-[10px] uppercase tracking-wider text-chalk">Pontos</span>
                                        <input type="number" wire:model="cofres.{{ $i }}.points" class="px-2 py-1 rounded-card border border-rule focus:border-ignite outline-none font-mono text-sm">
                                    </label>
                                </div>
                                <div class="flex items-center gap-1 mt-1 shrink-0">
                                    <button type="button" wire:click="regenerateCofreQr({{ $i }})" class="text-chalk hover:text-ignite text-[10px] underline" title="Regenerar QR">QR</button>
                                    @if (count($cofres) > 1)
                                        <button type="button" wire:click="removeCofre({{ $i }})" wire:confirm="Remover este cofre?" class="text-ember font-display font-semibold text-sm">x</button>
                                    @endif
                                </div>
                            </div>
                            @if (!empty($c['qr_code_uuid']))
                                <p class="font-mono text-[9px] text-chalk mt-1 ml-11 break-all">@php echo $c['qr_code_uuid']; @endphp</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
     </div>

        <div class="md:col-span-2 flex items-end justify-end pt-2">
            <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold hover:bg-ember transition-colors duration-200">
                @php echo $this->stage?->exists ? "Salvar" : "Criar"; @endphp enigma final
            </button>
     </div>
 </form>
</div>
