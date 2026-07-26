<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Enigma Final</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Palavra + Letras por QR</h1>
        <p class="text-chalk mt-2">Defina a palavra final e distribua letras em QR Codes espalhados pela escola</p>
 </header>

    <form wire:submit.prevent="save" class="bg-white rounded-card border border-rule p-6 shadow-card grid grid-cols-1 md:grid-cols-2 gap-5">
        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Competicao</span>
            <select wire:model="competition_id" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
                <option value="">Selecione</option>
                @foreach ($this->competitionsList as $c)
                    <option value="@php echo $c->id; @endphp">@php echo $c->name; @endphp (@php echo $c->year; @endphp</option>
                @endforeach
         </select>
            @error("competition_id") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
     </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Palavra final</span>
            <input type="text" maxlength="50" wire:model="word" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono uppercase">
            @error("word") <span class="text-ember text-xs">@php echo $message; @endphp</span> @enderror
   </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Max tentativas</span>
            <input type="number" min="1" max="10" wire:model="max_attempts" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
   </label>

        <label class="flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Cooldown (min</span>
            <input type="number" min="0" max="1440" wire:model="cooldown_minutes" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none font-mono">
   </label>

        <label class="md:col-span-2 flex flex-col gap-1">
            <span class="font-display text-xs uppercase tracking-wider text-chalk">Descricao</span>
            <textarea wire:model="description" rows="3" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none</textarea>
     </label>

        <div class="md:col-span-2 pt-2 border-t border-rule">
            <header class="flex items-center justify-between mb-3">
                <h3 class="font-display font-bold">QR Codes com Letras</h3>
                <button type="button" wire:click="addLetter" class="text-ignite font-display font-semibold">+ Adicionar letra</button>
        </header>
            @php $qrs = $qrCodes; @endphp
            <ol class="space-y-2">
                @foreach ($qrs as $i => $q)
                    <li class="flex items-center gap-3 bg-paper rounded-card p-3 border border-rule">
                        <span class="font-display font-bold text-ember w-8 text-center">@php echo $loop->iteration; @endphp</span>
                        <input type="text" maxlength="1" placeholder="letra" wire:model="qrCodes.{{ $i }}.letter" class="font-display font-bold text-2xl text-center w-16 px-2 py-2 rounded-card border border-rule uppercase">
                        <input type="text" placeholder="dica" wire:model="qrCodes.{{ $i }}.hint_text" class="flex-1 px-3 py-2 rounded-card border border-rule">
                        @if (count($qrs) > 1)
                            <button type="button" wire:click="removeLetter(@php echo $i; @endphp)" wire:confirm="Remover esta letra?" class="text-ember font-display font-semibold">Remover</button>
                        @endif
                 </li>
                @endforeach
         </ol>
     </div>

        <div class="md:col-span-2 flex items-end justify-end">
            <button type="submit" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold">@php echo $this->enigma?->exists ? "Salvar" : "Criar"; @endphp enigma</button>
     </div>
 </form>
</div>