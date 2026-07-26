<div wire:poll.5s>
    <div
        x-data="telaoData()"
        x-init="initTelao()"
        wire:ignore.self
        class="flex flex-col h-full w-full p-6 gap-4"
        style="font-size:18px;"
    >
        {{-- ======== HEADER ======== --}}
        <header class="flex items-center justify-between border-b border-white/10 pb-3 shrink-0">
            <div class="flex items-center gap-4">
                <span class="text-ignite text-xs uppercase tracking-[0.18em] font-display font-extrabold">Helena Quest</span>
                <h1 class="font-display font-extrabold text-4xl tracking-tight">{{ $this->competition->name }}</h1>
            </div>
            <div class="flex items-center gap-6 text-sm font-mono">
                <span class="text-chalk">{{ $this->competition->date?->format('d/m/Y') }}</span>
                <span class="px-3 py-1 rounded-pill text-xs font-semibold uppercase tracking-wider
                    @php
                        $s = $this->competition->status;
                        echo match($s) { 'active' => 'bg-green-900/40 text-green-400', 'finished' => 'bg-ember/20 text-ember', default => 'bg-white/10 text-chalk' };
                    @endphp
                ">{{ $s }}</span>
                <span class="text-chalk" x-text="clock"></span>
            </div>
        </header>

        {{-- ======== MAIN GRID (3 columns) — Livewire updates these via wire:poll ======== --}}
        <div class="flex-1 grid grid-cols-12 gap-4 min-h-0">

            {{-- COL 1: RANKING (3/12) --}}
            <section class="col-span-3 flex flex-col min-h-0">
                <h2 class="font-display font-bold text-xl text-ignite mb-3 uppercase tracking-wider text-sm">Classificacao</h2>
                <ol class="flex-1 space-y-2 overflow-y-auto scrollbar-thin pr-1">
                    @forelse ($this->ranking as $i => $r)
                        <li class="flex items-center gap-3 bg-white/[0.03] rounded-card px-4 py-3 border-l-[3px]" style="border-left-color: {{ $r['color_hex'] }};">
                            <span class="font-display font-extrabold text-2xl text-chalk w-8 text-center shrink-0">{{ $i + 1 }}</span>
                            @if ($r['crest_url'])
                                <img src="@php echo $r['crest_url']; @endphp" alt="" class="w-8 h-8 rounded-full object-cover shrink-0">
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-display font-bold text-lg truncate">{{ $r['name'] }}</p>
                                <p class="text-chalk text-xs font-mono">{{ $r['total_stages'] }} etapas &middot; {{ gmdate('H:i:s', $r['total_time']) }}</p>
                            </div>
                            <span class="font-display font-extrabold text-3xl text-ignite tabular-nums">{{ $r['total_score'] }}</span>
                        </li>
                    @empty
                        <li class="text-chalk">Nenhuma equipe ativa</li>
                    @endforelse
                </ol>
            </section>

            {{-- COL 2: PROGRESSO (5/12) --}}
            <section class="col-span-5 flex flex-col min-h-0">
                <h2 class="font-display font-bold text-xl text-ignite mb-3 uppercase tracking-wider text-sm">Progresso</h2>
                <div class="flex-1 space-y-4 overflow-y-auto scrollbar-thin pr-1">
                    @forelse ($this->progress as $proof)
                        <div>
                            <h3 class="font-display font-semibold text-base text-paper mb-2 flex items-center gap-2">
                                @if ($proof['color_hex'])
                                    <span class="w-2 h-2 rounded-full inline-block" style="background:{{ $proof['color_hex'] }}"></span>
                                @endif
                                {{ $proof['name'] }}
                            </h3>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach ($proof['stages'] as $st)
                                    @php
                                        $pct = $st['total'] > 0 ? round(($st['completed_count'] / $st['total']) * 100) : 0;
                                        $isAnyActive = $st['active_count'] > 0;
                                    @endphp
                                    <div class="bg-white/[0.03] rounded-card px-2 py-2 text-center {{ $isAnyActive ? 'ring-1 ring-ignite/40 pulse-glow' : '' }}">
                                        <p class="text-[10px] text-chalk font-mono truncate leading-tight">{{ $st['name'] }}</p>
                                        <p class="font-display font-bold text-xl text-ignite tabular-nums">{{ $st['completed_count'] }}/{{ $st['total'] }}</p>
                                        <div class="w-full h-1.5 bg-white/10 rounded-full mt-1 overflow-hidden">
                                            <div class="bar-fill h-full bg-ignite rounded-full" style="width:{{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-chalk">Nenhuma prova cadastrada</p>
                    @endforelse
                </div>
            </section>

            {{-- COL 3: GRID 4×3 DAS EQUIPES (4/12) --}}
            <section class="col-span-4 flex flex-col min-h-0">
                <h2 class="font-display font-bold text-xl text-ignite mb-3 uppercase tracking-wider text-sm">Equipes ao Vivo</h2>
                <div class="flex-1 grid-4x3">
                    @php
                        $activeTeams = $this->competition->teams->where('status', 'active')->values();
                        $locations = collect($this->teamLocations)->keyBy('team_id');
                        $rankingLookup = collect($this->ranking)->keyBy('id');
                        $finalStatus = $this->finalEnigmaStatus;
                        $finalTeams = collect($finalStatus['teams'] ?? [])->keyBy('team_id');
                    @endphp
                    @forelse ($activeTeams as $cellIdx => $team)
                        @php
                            $loc = $locations->get($team->id);
                            $rank = $rankingLookup->get($team->id);
                            $hasGps = $loc && $loc['lat'] !== null;
                            $fs = $finalTeams->get($team->id);
                        @endphp
                        <div class="zone-cell {{ $hasGps ? 'active' : '' }}" style="border-left-color: {{ $team->color_hex }}; border-left-width: 3px;">
                            <div class="flex items-center gap-2 mb-1">
                                @if ($rank)
                                    <span class="font-display font-extrabold text-lg text-chalk">#{{ $activeTeams->search(fn($t) => $t->id === $team->id) + 1 }}</span>
                                @endif
                                <p class="font-display font-bold text-sm truncate" style="color:{{ $team->color_hex }}">{{ $team->name }}</p>
                            </div>
                            @if ($rank)
                                <div class="flex items-baseline gap-2">
                                    <span class="font-display font-extrabold text-3xl tabular-nums text-ignite">{{ $rank['total_score'] }}</span>
                                    <span class="text-chalk text-[10px] font-mono">pts</span>
                                </div>
                                <div class="flex gap-2 text-[10px] font-mono text-chalk mt-1">
                                    <span>{{ $rank['total_stages'] }} etapas</span>
                                    <span>{{ $rank['photos_count'] }} fotos</span>
                                    <span>{{ $rank['audios_count'] }} audios</span>
                                </div>
                            @else
                                <div class="text-chalk text-xs mt-2">—</div>
                            @endif
                            @if ($fs)
                                @if ($fs['solved'])
                                    <span class="text-[9px] text-green-400 font-mono mt-1">★ Enigma resolvido!</span>
                                @elseif ($fs['letters_collected'] >= $fs['required_letters'])
                                    <span class="text-[9px] text-flame font-mono mt-1">★ Todas as letras coletadas</span>
                                @elseif ($fs['letters_collected'] > 0)
                                    <span class="text-[9px] text-chalk font-mono mt-1">{{ $fs['letters_collected'] }}/{{ $fs['required_letters'] }} letras</span>
                                @endif
                            @elseif ($hasGps)
                                <span class="text-[9px] text-green-400 font-mono mt-1">● online</span>
                            @endif
                        </div>
                    @empty
                        <div class="zone-cell col-span-4 row-span-3 text-chalk text-lg">Nenhuma equipe ativa</div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- ======== BOTTOM BAR: Carrossel + Audio (wire:ignore sections) ======== --}}
        <div class="grid grid-cols-12 gap-4 border-t border-white/10 pt-3 min-h-[180px] max-h-[220px] shrink-0">

            {{-- Carrossel de Fotos (8/12) --}}
            <section class="col-span-8 flex flex-col" wire:ignore>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-display font-bold text-sm text-ignite uppercase tracking-wider">Fotos</h3>
                    <div class="flex gap-1">
                        <template x-for="(_, idx) in photos" :key="idx">
                            <button
                                class="w-2 h-2 rounded-full transition-colors duration-300"
                                :class="idx === photoIndex ? 'bg-ignite' : 'bg-white/20'"
                                @@click="photoIndex = idx; pauseCarousel()"
                            ></button>
                        </template>
                    </div>
                </div>
                <div class="flex-1 relative bg-white/[0.02] rounded-card overflow-hidden">
                    <template x-for="(photo, idx) in photos" :key="photo.id">
                        <div
                            x-show="idx === photoIndex"
                            x-transition:enter="transition-opacity duration-800"
                            class="absolute inset-0 flex items-center justify-center"
                        >
                            <img :src="photo.photo_url" :alt="'Foto de ' + photo.team_name" class="max-h-full max-w-full object-contain rounded-lg">
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-ink/80 to-transparent px-3 py-2">
                                <span class="text-xs font-mono" :style="'color:' + photo.team_color" x-text="photo.team_name"></span>
                                <span class="text-[10px] text-chalk ml-2 font-mono" x-text="photo.sent_at ? new Date(photo.sent_at).toLocaleTimeString('pt-BR') : ''"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="photos.length === 0" class="absolute inset-0 flex items-center justify-center text-chalk text-xs">
                        Aguardando fotos...
                    </div>
                </div>
            </section>

            {{-- Player de Audio (4/12) --}}
            <section class="col-span-4 flex flex-col" wire:ignore>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-display font-bold text-sm text-ignite uppercase tracking-wider">Audio</h3>
                    <span class="text-[10px] font-mono text-chalk" x-text="audios.length + ' na fila'"></span>
                </div>
                <div class="flex-1 bg-white/[0.02] rounded-card p-3 flex flex-col">
                    <div x-show="audios.length > 0" class="mb-2">
                        <p class="text-[11px] font-mono text-chalk truncate">
                            <span class="text-green-400">&#9654;</span>
                            <span x-text="audios[audioIndex]?.team_name ?? ''"></span>
                        </p>
                        <p class="text-xs truncate" x-text="audios[audioIndex]?.stage_name ?? ''"></p>
                    </div>
                    <audio x-ref="audioPlayer" @@ended="playNextAudio()" class="hidden" controls></audio>
                    <div class="flex-1 overflow-y-auto scrollbar-thin space-y-1 text-[11px] font-mono">
                        <template x-for="(a, idx) in audios" :key="a.id">
                            <div
                                class="flex items-center gap-2 px-1 py-0.5 rounded cursor-pointer"
                                :class="idx === audioIndex ? 'bg-ignite/10 text-ignite' : 'text-chalk hover:text-paper'"
                                @@click="playAudio(idx)"
                            >
                                <span x-text="idx === audioIndex ? '&#9654;' : '&#9834;'"></span>
                                <span class="truncate flex-1" x-text="a.team_name"></span>
                                <span class="text-[9px]" x-text="a.duration_seconds + 's'"></span>
                            </div>
                        </template>
                        <div x-show="audios.length === 0" class="text-chalk text-center pt-4">
                            Aguardando audios...
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Footer --}}
        <footer class="text-center text-chalk text-[10px] font-mono border-t border-white/5 pt-2 shrink-0">
            Helena Quest &middot; atualizado em {{ now()->format('H:i:s') }}
        </footer>
    </div>
</div>

<script>
    function telaoData() {
        return {
            photos: [],
            photoIndex: 0,
            carouselTimer: null,

            audios: [],
            audioIndex: 0,
            isPlaying: false,
            audioEl: null,

            clock: '',
            echo: null,

            initTelao() {
                this.startClock();
                this.loadFromServer();
                this.startCarousel();
                this.initEcho();
            },

            loadFromServer() {
                if (window.Livewire && this.$wire) {
                    this.$wire.get('recentPhotos').then(data => { this.photos = data; });
                    this.$wire.get('recentAudios').then(data => { this.audios = data; });
                }
            },

            startClock() {
                const tick = () => {
                    this.clock = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                };
                tick();
                setInterval(tick, 1000);
            },

            startCarousel() {
                this.stopCarousel();
                this.carouselTimer = setInterval(() => {
                    if (this.photos.length > 0) {
                        this.photoIndex = (this.photoIndex + 1) % this.photos.length;
                    }
                }, 8000);
            },
            stopCarousel() {
                if (this.carouselTimer) { clearInterval(this.carouselTimer); this.carouselTimer = null; }
            },
            pauseCarousel() {
                this.stopCarousel();
                setTimeout(() => { this.startCarousel(); }, 12000);
            },

            playAudio(idx) {
                if (!this.audios.length) return;
                this.audioIndex = idx;
                const src = this.audios[idx]?.audio_url;
                if (src && this.audioEl) {
                    this.audioEl.src = src;
                    this.audioEl.play().catch(() => {});
                }
            },
            playNextAudio() {
                if (this.audioIndex < this.audios.length - 1) {
                    this.playAudio(this.audioIndex + 1);
                } else {
                    this.isPlaying = false;
                }
            },

            initEcho() {
                if (typeof Echo === 'undefined' || typeof Pusher === 'undefined') return;
                try {
                    this.echo = new Echo({
                        broadcaster: 'pusher',
                        key: '{{ config("broadcasting.connections.reverb.key") }}',
                        wsHost: '{{ config("broadcasting.connections.reverb.options.host") }}',
                        wsPort: {{ config("broadcasting.connections.reverb.options.port") }},
                        wssPort: {{ config("broadcasting.connections.reverb.options.port") }},
                        forceTLS: {{ config("broadcasting.connections.reverb.options.useTLS") ? 'true' : 'false' }},
                        disableStats: true,
                        enabledTransports: ['ws', 'wss'],
                    });

                    this.echo.channel('competition.{{ $this->competitionId }}')
                        .listen('.stage.updated', () => { this.refreshFromServer(); })
                        .listen('.score.updated', () => { this.refreshFromServer(); })
                        .listen('.photo.sent', () => { this.refreshFromServer(); })
                        .listen('.audio.sent', () => { this.refreshFromServer(); })
                        .listen('.location.updated', () => { this.refreshFromServer(); })
                        .listen('.competition.status', () => { this.refreshFromServer(); });
                } catch (e) {
                    console.warn('Echo not available:', e.message);
                }
            },

            refreshFromServer() {
                this.loadFromServer();
                if (window.Livewire && this.$wire) {
                    this.$wire.$refresh();
                }
            },
        };
    }

    document.addEventListener('livewire:updated', () => {
        const el = document.querySelector('[x-data]');
        if (el && el.__x) {
            el.__x.loadFromServer();
        }
    });
</script>
