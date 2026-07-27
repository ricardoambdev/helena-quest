<div wire:poll.3s class="relative h-screen w-full overflow-hidden bg-[#1a1d23] font-display">
    <div
        x-data="telaoData()"
        x-init="initTelao()"
        wire:ignore.self
        class="absolute inset-0"
    >
        {{-- MAPA FULL-SCREEN --}}
        <div id="telao-map" class="absolute inset-0 z-0"></div>
        <div id="map-fallback" class="absolute inset-0 z-0 flex items-center justify-center text-white/40 text-lg">
            Carregando mapa...
        </div>

        {{-- OVERLAY: TITULO + RELOGIO (topo central) --}}
        <div class="absolute top-0 left-0 right-0 z-10 bg-gradient-to-b from-black/70 to-transparent px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="font-display font-extrabold text-3xl text-white drop-shadow-lg">{{ $this->competition->name }}</h1>
                <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/20 text-white/80">
                    {{ $this->competition->status }}
                </span>
            </div>
            <div class="flex items-center gap-4 text-white/80 font-mono text-lg" x-text="clock"></div>
        </div>

        {{-- OVERLAY: PLACAR (canto direito) --}}
        <div class="absolute top-20 right-4 z-10 w-72 space-y-3">
            @forelse ($this->ranking as $i => $r)
                <div class="backdrop-blur-md bg-black/50 rounded-2xl border border-white/10 px-5 py-4 shadow-2xl"
                     style="border-left: 4px solid {{ $r['color_hex'] }};">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-mono font-bold text-3xl text-white/40 w-8 shrink-0">{{ $i + 1 }}º</span>
                        @if ($r['crest_url'])
                            <img src="{{ $r['crest_url'] }}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0 ring-2" style="ring-color: {{ $r['color_hex'] }};">
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-display font-bold text-xl text-white truncate drop-shadow">{{ $r['name'] }}</p>
                            <p class="text-white/50 text-sm font-mono">{{ $r['current_stage'] }}</p>
                        </div>
                        <span class="font-display font-extrabold text-4xl tabular-nums drop-shadow" style="color: {{ $r['color_hex'] }};">
                            {{ $r['total_score'] }}
                        </span>
                    </div>
                    <div class="flex gap-4 text-white/40 text-xs font-mono">
                        <span>{{ $r['total_stages'] }} etapas</span>
                        <span>{{ gmdate('H:i:s', $r['total_time']) }}</span>
                    </div>
                </div>
            @empty
                <div class="backdrop-blur-md bg-black/50 rounded-2xl px-5 py-4 text-white/50">
                    Nenhuma equipe ativa
                </div>
            @endforelse
        </div>

        {{-- OVERLAY: FOTOS + AUDIO (parte inferior) --}}
        <div class="absolute bottom-0 left-0 right-0 z-10 bg-gradient-to-t from-black/70 to-transparent px-6 pb-4 pt-12">
            <div class="grid grid-cols-12 gap-4">

                {{-- FOTOS (8/12) — thumbnails grid --}}
                <section class="col-span-8" wire:ignore>
                    <h3 class="font-display font-bold text-sm text-white/60 uppercase tracking-wider mb-2 drop-shadow">
                        Fotos
                    </h3>
                    <div class="flex gap-2 overflow-x-auto scrollbar-thin pb-1"
                         x-ref="photoStrip">
                        <template x-for="(photo, idx) in photos" :key="photo.id">
                            <div class="shrink-0 cursor-pointer transition-transform duration-200 hover:scale-105"
                                 @@click="openPhotoModal(idx)">
                                <img :src="photo.photo_url"
                                     :alt="'Foto de ' + photo.team_name"
                                     class="h-20 w-20 object-cover rounded-xl ring-1 ring-white/10">
                                <p class="text-[10px] font-mono text-white/50 mt-1 truncate w-20"
                                   :style="'color:' + photo.team_color"
                                   x-text="photo.team_name"></p>
                            </div>
                        </template>
                        <div x-show="photos.length === 0"
                             class="h-20 w-40 flex items-center justify-center text-white/30 text-xs rounded-xl border border-dashed border-white/10">
                            Aguardando fotos...
                        </div>
                    </div>
                </section>

                {{-- AUDIO (4/12) --}}
                <section class="col-span-4" wire:ignore>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-display font-bold text-sm text-white/60 uppercase tracking-wider drop-shadow">
                            Audio
                        </h3>
                        <span class="text-xs font-mono text-white/40" x-text="audios.length + ' na fila'"></span>
                    </div>
                    <div class="backdrop-blur-md bg-black/40 rounded-xl p-3">
                        <div x-show="audios.length > 0" class="mb-2 text-sm">
                            <p class="font-mono text-white/80 truncate">
                                <span class="text-green-400 mr-1">&#9654;</span>
                                <span x-text="audios[audioIndex]?.team_name ?? ''"></span>
                            </p>
                            <p class="text-xs text-white/40 truncate" x-text="audios[audioIndex]?.stage_name ?? ''"></p>
                        </div>
                        <audio x-ref="audioPlayer" @@ended="playNextAudio()" class="hidden" controls></audio>
                        <div class="max-h-24 overflow-y-auto scrollbar-thin space-y-1 text-xs font-mono">
                            <template x-for="(a, idx) in audios" :key="a.id">
                                <div class="flex items-center gap-2 px-2 py-1 rounded cursor-pointer transition-colors"
                                     :class="idx === audioIndex ? 'bg-white/10 text-white' : 'text-white/50 hover:text-white/80'"
                                     @@click="playAudio(idx)">
                                    <span x-text="idx === audioIndex ? '&#9654;' : '&#9834;'"></span>
                                    <span class="truncate flex-1" x-text="a.team_name"></span>
                                    <span class="text-[10px] text-white/30" x-text="a.duration_seconds + 's'"></span>
                                </div>
                            </template>
                            <div x-show="audios.length === 0" class="text-white/30 text-center py-4">
                                Aguardando audios...
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        {{-- MODAL DE FOTO (tela inteira, 10s) --}}
        <template x-teleport="body">
            <div x-show="modalPhoto !== null"
                 x-transition:enter="transition-opacity duration-300"
                 x-transition:leave="transition-opacity duration-300"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/90"
                 @@click="closePhotoModal()">
                <template x-if="modalPhoto !== null">
                    <img :src="photos[modalPhoto]?.photo_url"
                         :alt="'Foto de ' + photos[modalPhoto]?.team_name"
                         class="max-h-[90vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl">
                </template>
                <p class="absolute bottom-6 left-0 right-0 text-center text-white/60 text-sm font-mono drop-shadow"
                   x-text="(photos[modalPhoto]?.team_name ?? '') + ' — ' + (photos[modalPhoto]?.sent_at ? new Date(photos[modalPhoto].sent_at).toLocaleTimeString('pt-BR') : '')">
                </p>
            </div>
        </template>

        {{-- LEGENDA OPENSTREETMAP --}}
        <div class="absolute bottom-1 left-2 z-10 text-[9px] text-white/20 font-mono pointer-events-none">
            &copy; OpenStreetMap contributors
        </div>
    </div>
</div>

<script>
    function telaoData() {
        return {
            photos: [],
            photoIndex: 0,
            carouselTimer: null,
            modalPhoto: null,
            modalTimer: null,

            audios: [],
            audioIndex: 0,
            isPlaying: false,
            audioEl: null,

            clock: '',
            echo: null,

            map: null,
            mapMarkers: [],
            mapInitialized: false,
            schoolMarker: null,

            initTelao() {
                this.startClock();
                this.loadFromServer();
                this.startCarousel();
                this.initEcho();
                this.initMap();
            },

            loadFromServer() {
                if (window.Livewire && this.$wire) {
                    this.$wire.get('recentPhotos').then(data => { this.photos = data; });
                    this.$wire.get('recentAudios').then(data => { this.audios = data; });
                }
            },

            initMap() {
                var el = document.getElementById('telao-map');
                if (!el) return;
                var fallback = document.getElementById('map-fallback');
                try {
                    this.map = L.map(el, {
                        zoom: 15,
                        center: [-21.996, -47.426],
                        zoomControl: false,
                        attributionControl: false,
                    });
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                    }).addTo(this.map);
                    if (fallback) fallback.style.display = 'none';
                    this.mapInitialized = true;
                    this.loadSchoolMarker();
                    this.loadMapLocations();
                } catch (e) {
                    if (fallback) fallback.textContent = 'Erro ao carregar mapa: ' + e.message;
                }
            },

            loadSchoolMarker() {
                if (!this.mapInitialized || !this.$wire) return;
                this.$wire.get('schoolLocation').then(function(school) {
                    if (this.schoolMarker) { this.map.removeLayer(this.schoolMarker); }
                    if (!school) return;
                    var icon = L.divIcon({
                        className: 'school-pin',
                        html: '<div style="width:32px;height:32px;background:#FF6600;border-radius:50%;border:3px solid #fff;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.5);">' +
                            (school.logo ? '<img src="' + school.logo + '" style="width:20px;height:20px;border-radius:50%;object-fit:cover;">' : '<span style="color:#fff;font-weight:bold;font-size:14px;">E</span>') +
                            '</div>',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                    });
                    this.schoolMarker = L.marker([school.lat, school.lng], { icon: icon })
                        .addTo(this.map)
                        .bindTooltip(school.name, { permanent: false, direction: 'top' });
                }.bind(this));
            },

            loadMapLocations() {
                if (!this.mapInitialized || !this.$wire) return;
                this.$wire.get('teamLocations').then(function(locs) {
                    this.updateMapMarkers(locs);
                }.bind(this));
            },

            updateMapMarkers(locations) {
                this.mapMarkers.forEach(function(m) { this.map.removeLayer(m); }.bind(this));
                this.mapMarkers = [];
                locations.forEach(function(loc) {
                    if (loc.lat == null || loc.lng == null) return;
                    var crest = loc.crest_url;
                    var html = crest
                        ? '<img src="' + crest + '" style="width:40px;height:40px;border-radius:50%;border:3px solid ' + loc.team_color + ';object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,.5);">'
                        : '<div style="width:40px;height:40px;border-radius:50%;background:' + loc.team_color + ';border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.5);"></div>';
                    var icon = L.divIcon({
                        className: 'team-marker',
                        html: html,
                        iconSize: [40, 40],
                        iconAnchor: [20, 20],
                    });
                    var m = L.marker([loc.lat, loc.lng], { icon: icon })
                        .addTo(this.map)
                        .bindTooltip(loc.team_name, { permanent: false, direction: 'top' });
                    this.mapMarkers.push(m);
                }.bind(this));
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

            openPhotoModal(idx) {
                this.modalPhoto = idx;
                if (this.modalTimer) clearTimeout(this.modalTimer);
                this.modalTimer = setTimeout(() => { this.closePhotoModal(); }, 10000);
                this.stopCarousel();
            },
            closePhotoModal() {
                this.modalPhoto = null;
                if (this.modalTimer) { clearTimeout(this.modalTimer); this.modalTimer = null; }
                this.startCarousel();
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
                this.loadMapLocations();
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
            el.__x.loadMapLocations();
        }
    });
</script>
