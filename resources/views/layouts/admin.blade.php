<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Helena Quest — Painel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ignite: '#FF6600',
                        ember: '#CC5200',
                        flame: '#FF8533',
                        ink: '#0D0D0F',
                        paper: '#FAF8F5',
                        chalk: '#7A7468',
                        rule: '#E0DCD3',
                    },
                    fontFamily: {
                        display: ['Inter', 'system-ui', 'sans-serif'],
                        body: ['Nunito', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    borderRadius: {
                        card: '12px',
                        pill: '9999px',
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|nunito:400,500,600,700|jetbrains-mono:400,500,700" rel="stylesheet" />
    @livewireStyles
    <style>
        :root { color-scheme: light; }
        body { font-family: 'Nunito', system-ui, sans-serif; }
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body class="bg-paper text-ink antialiased">
    <div class="min-h-screen flex">
        <aside class="w-60 bg-ink text-paper flex flex-col shrink-0">
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-baseline gap-2">
                    <span class="text-[11px] uppercase tracking-[0.18em] text-ignite font-display font-semibold">Helena</span>
                    <span class="font-display font-extrabold text-xl text-paper">Quest</span>
                </div>
            </div>
            @php
                $navigation = [
                    ['label' => 'Painel geral',     'route' => 'admin.dashboard',          'icon' => 'home'],
                    ['label' => 'Competições',      'route' => 'admin.competitions.index', 'icon' => 'trophy'],
                    ['label' => 'Provas',           'route' => 'admin.proofs.index',       'icon' => 'map'],
                    ['label' => 'Etapas',           'route' => 'admin.stages.index',       'icon' => 'qr'],
                    ['label' => 'Equipes',          'route' => 'admin.teams.index',        'icon' => 'users'],
                    ['label' => 'Enigma Final',     'route' => 'admin.final-enigma.index', 'icon' => 'key'],
                    ['label' => 'Telão (preview)',  'route' => 'telao.index',               'icon' => 'monitor'],
                    ['label' => 'Auditoria / Logs', 'route' => 'admin.logs.index',         'icon' => 'log'],
                ];
            @endphp
            <nav class="flex-1 px-3 space-y-0.5" aria-label="Navegação principal">
                @foreach ($navigation as $item)
                    @php
                        $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route']));
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-display font-semibold transition-colors duration-150
                              {{ $active ? 'bg-ignite text-ink' : 'text-chalk hover:text-paper hover:bg-white/5' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $active ? 'bg-ink' : 'bg-chalk/60' }}"></span>
                        @php echo $item['label']; @endphp
                    </a>
                @endforeach
            </nav>
            <div class="px-6 py-4 border-t border-white/10">
                <div class="text-xs text-chalk">
                    <p class="font-display font-semibold text-paper mb-0.5">{{ auth()->user()?->name ?? 'Admin' }}</p>
                    <p>{{ auth()->user()?->email ?? '—' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-xs text-chalk hover:text-paper transition-colors duration-150">Sair</button>
                </form>
            </div>
        </aside>
        <main class="flex-1 p-8 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
