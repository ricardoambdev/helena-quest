<div class="max-w-2xl">
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Configurações</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Preferências do sistema</h1>
        <p class="text-chalk mt-1">Configurações gerais da plataforma</p>
    </header>

    <form wire:submit="save">
        {{-- ESCOLA --}}
        <div class="bg-white rounded-xl border border-rule p-6 space-y-5 mb-6">
            <h2 class="font-display font-bold text-lg text-ink flex items-center gap-2">
                <svg class="w-5 h-5 text-ignite" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Escola
            </h2>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-display font-semibold text-ink mb-1">Nome da escola</label>
                    <input type="text" wire:model="preferences.school_name"
                           class="w-full rounded-lg border border-rule px-4 py-2.5 text-sm font-body text-ink focus:border-ignite focus:ring-1 focus:ring-ignite outline-none">
                    @error('preferences.school_name') <p class="text-ember text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-display font-semibold text-ink mb-1">Endereço</label>
                    <input type="text" wire:model="preferences.school_address"
                           class="w-full rounded-lg border border-rule px-4 py-2.5 text-sm font-body text-ink focus:border-ignite focus:ring-1 focus:ring-ignite outline-none">
                    @error('preferences.school_address') <p class="text-ember text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-display font-semibold text-ink mb-1">Latitude</label>
                    <input type="text" wire:model="preferences.school_latitude" placeholder="-23.5505"
                           class="w-full rounded-lg border border-rule px-4 py-2.5 text-sm font-mono text-ink focus:border-ignite focus:ring-1 focus:ring-ignite outline-none">
                    @error('preferences.school_latitude') <p class="text-ember text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-display font-semibold text-ink mb-1">Longitude</label>
                    <input type="text" wire:model="preferences.school_longitude" placeholder="-47.426"
                           class="w-full rounded-lg border border-rule px-4 py-2.5 text-sm font-mono text-ink focus:border-ignite focus:ring-1 focus:ring-ignite outline-none">
                    @error('preferences.school_longitude') <p class="text-ember text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-display font-semibold text-ink mb-1">Logo da escola</label>
                    <div class="flex items-center gap-4">
                        @if ($preferences['school_logo_path'] ?? false)
                            <div class="relative">
                                <img src="{{ Storage::url($preferences['school_logo_path']) }}"
                                     class="w-16 h-16 rounded-xl object-cover border border-rule">
                                <button type="button" wire:click="removeLogo"
                                        class="absolute -top-2 -right-2 w-5 h-5 bg-ember text-white rounded-full text-xs flex items-center justify-center hover:bg-red-700">
                                    &times;
                                </button>
                            </div>
                        @endif
                        <input type="file" wire:model="schoolLogo" accept="image/png,image/jpeg"
                               class="text-sm text-chalk file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-display file:font-semibold file:bg-ignite file:text-white hover:file:bg-ember cursor-pointer">
                    </div>
                    @error('schoolLogo') <p class="text-ember text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- TELAO --}}
        <div class="bg-white rounded-xl border border-rule p-6 space-y-5 mb-6">
            <h2 class="font-display font-bold text-lg text-ink flex items-center gap-2">
                <svg class="w-5 h-5 text-ignite" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Telão
            </h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-display font-semibold text-ink mb-1">Zoom do mapa</label>
                    <input type="number" wire:model="preferences.telao_map_zoom" min="1" max="19"
                           class="w-full rounded-lg border border-rule px-4 py-2.5 text-sm font-mono text-ink focus:border-ignite focus:ring-1 focus:ring-ignite outline-none">
                    @error('preferences.telao_map_zoom') <p class="text-ember text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-display font-semibold text-ink mb-1">Atualizar a cada (s)</label>
                    <input type="number" wire:model="preferences.telao_refresh_seconds" min="1" max="60"
                           class="w-full rounded-lg border border-rule px-4 py-2.5 text-sm font-mono text-ink focus:border-ignite focus:ring-1 focus:ring-ignite outline-none">
                    @error('preferences.telao_refresh_seconds') <p class="text-ember text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="flex items-center gap-4">
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-ignite text-white font-display font-semibold text-sm hover:bg-ember transition-colors duration-200">
                Salvar configurações
            </button>
            @if (session('message'))
                <p class="text-green-600 text-sm font-display font-semibold">{{ session('message') }}</p>
            @endif
        </div>
    </form>
</div>
