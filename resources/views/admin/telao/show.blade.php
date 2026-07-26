@php
    use App\Models\TeamProgress;
    $teams = $competition->teams->where('status', 'active');
    $proofs = $competition->proofs->sortBy('order');

    $ranking = TeamProgress::whereIn('team_id', $teams->pluck('id'))
        ->selectRaw('team_id, SUM(total_score) as total_score, SUM(total_time_seconds) as total_time, SUM(stages_completed) as total_stages')
        ->groupBy('team_id')
        ->get()
        ->keyBy('team_id');

    $sortedTeams = $teams->sortByDesc(fn ($t) => $ranking->get($t->id)?->total_score ?? 0);

    $colegioLat = -21.9965;
    $colegioLng = -47.4265;

    $teamPositions = [];
    foreach ($teams as $t) {
        $lastProgress = $t->stageProgress()->whereNotNull('gps_lat')->latest()->first();
        $teamPositions[] = [
            'id' => $t->id,
            'name' => $t->name,
            'color' => $t->color_hex,
            'lat' => $lastProgress?->gps_lat ?? $colegioLat,
            'lng' => $lastProgress?->gps_lng ?? $colegioLng,
            'crest' => $t->crest_path ? asset('storage/' . $t->crest_path) : null,
        ];
    }
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $competition->name }} — Telao</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{ignite:'#FF6600',ember:'#CC5200',flame:'#FF8533',ink:'#171A21',paper:'#FAF8F5',chalk:'#7A7468',rule:'#E0DCD3'},fontFamily:{display:['Inter','system-ui','sans-serif'],body:['Nunito','system-ui','sans-serif'],mono:['JetBrains Mono','monospace']},borderRadius:{card:'12px',pill:'9999px'}}}}</script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700,800,900|nunito:400,600,700|jetbrains-mono:400,500,700" rel="stylesheet" />
    <style>
        body { font-family: 'Nunito', system-ui, sans-serif; background: #171A21; color: #FAF8F5; }
        #map { width: 100%; height: 100%; min-height: 400px; border-radius: 12px; }
        @media (prefers-reduced-motion: reduce) { *,::before,::after { animation-duration:0.01ms !important; transition-duration:0.01ms !important; } }
    </style>
</head>
<body class="min-h-screen flex flex-col p-8" style="font-size:18px;">
    <header class="flex items-baseline justify-between mb-8 border-b border-white/10 pb-4">
        <div>
            <span class="text-ignite text-xs uppercase tracking-[0.18em] font-display font-semibold">Helena Quest ao Vivo</span>
            <h1 class="font-display font-extrabold text-5xl mt-1">{{ $competition->name }}</h1>
        </div>
        <div class="text-right">
            <p class="text-chalk text-sm font-mono">{{ $competition->date?->format('d/m/Y') }}</p>
            <p class="text-xs uppercase tracking-wider text-chalk mt-1">{{ $competition->status }}</p>
        </div>
    </header>

    <div class="grid grid-cols-5 gap-6 flex-1">
        <section class="col-span-2 flex flex-col gap-6">
            <div>
                <h2 class="font-display font-bold text-2xl text-ignite mb-4">Classificacao</h2>
                <ol class="space-y-3">
                    @forelse ($sortedTeams as $i => $team)
                        @php $r = $ranking->get($team->id); @endphp
                        <li class="flex items-center gap-4 bg-white/5 rounded-card px-5 py-4 border-l-4" style="border-left-color: {{ $team->color_hex }};">
                            <span class="font-display font-extrabold text-3xl text-chalk w-10">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="font-display font-bold text-xl truncate">{{ $team->name }}</p>
                                <p class="text-chalk text-sm">{{ $r?->total_stages ?? 0 }} etapas &middot; {{ $r?->total_time ?? 0 }}s</p>
                            </div>
                            <span class="font-display font-extrabold text-3xl text-ignite">{{ $r?->total_score ?? 0 }}</span>
                        </li>
                    @empty
                        <li class="text-chalk">Nenhuma equipe ativa</li>
                    @endforelse
                </ol>
            </div>

            <div class="flex-1">
                <h2 class="font-display font-bold text-2xl text-ignite mb-4">Mapa</h2>
                <div id="map"></div>
            </div>
        </section>

        <section class="col-span-3">
            <h2 class="font-display font-bold text-2xl text-ignite mb-4">Progresso</h2>
            <div class="space-y-6">
                @forelse ($proofs as $proof)
                    <div>
                        <h3 class="font-display font-semibold text-lg text-paper mb-2">{{ $proof->name }}</h3>
                        <div class="flex gap-2">
                            @foreach ($proof->stages as $st)
                                @php
                                    $completedCount = 0;
                                    $anyActive = false;
                                    foreach ($teams as $tm) {
                                        $sp = $tm->stageProgress->where('stage_id', $st->id)->first();
                                        if ($sp && $sp->status === 'completed') $completedCount++;
                                        if ($sp && in_array($sp->status, ['active','photo_sent','answered_wrong','answered_correct'])) $anyActive = true;
                                    }
                                    $total = $teams->count();
                                    $pct = $total > 0 ? round(($completedCount / $total) * 100) : 0;
                                @endphp
                                <div class="flex-1 bg-white/5 rounded-card px-3 py-2 text-center {{ $anyActive ? 'ring-2 ring-ignite' : '' }}">
                                    <p class="text-xs text-chalk font-mono truncate">{{ $st->name }}</p>
                                    <p class="font-display font-bold text-lg text-ignite">{{ $completedCount }}/{{ $total }}</p>
                                    <div class="w-full h-1.5 bg-white/10 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-ignite rounded-full transition-all duration-500" style="width:{{ $pct }}%"></div>
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
    </div>

    <footer class="mt-auto pt-4 border-t border-white/10 text-center text-chalk text-xs font-mono">
        Helena Quest &middot; atualizado em {{ now()->format('H:i:s') }}
    </footer>

    <script>
        function initMap() {
            var center = { lat: -21.996, lng: -47.426 };
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: center,
                disableDefaultUI: false,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            @if (count($teamPositions) > 0)
                @foreach ($teamPositions as $pos)
                    new google.maps.Marker({
                        position: { lat: {{ $pos['lat'] }}, lng: {{ $pos['lng'] }} },
                        map: map,
                        title: '{{ $pos['name'] }}',
                        icon: { path: google.maps.SymbolPath.CIRCLE, scale: 14, fillColor: '{{ $pos['color'] }}', fillOpacity: 1, strokeWeight: 2, strokeColor: '#fff' },
                    });
                @endforeach
            @else
                new google.maps.Marker({
                    position: { lat: {{ $colegioLat }}, lng: {{ $colegioLng }} },
                    map: map,
                    title: 'Colégio Helena',
                    icon: { path: google.maps.SymbolPath.CIRCLE, scale: 14, fillColor: '#FF6600', fillOpacity: 1, strokeWeight: 2, strokeColor: '#fff' },
                });
            @endif
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" async defer></script>
</body>
</html>
