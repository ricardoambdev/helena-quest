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
                        ink: '#171A21',
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
        .nav-link { transition: all 0.15s ease; }
        .nav-link:hover { background: rgba(255,255,255,0.06); }
        .nav-link.active { background: rgba(255,102,0,0.12); color: #FF6600; }
        .nav-link.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: #FF6600; border-radius: 0 3px 3px 0; }
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body class="bg-paper text-ink antialiased">
    <div class="min-h-screen flex">
        <aside class="w-60 bg-[#171A21] flex flex-col shrink-0">
            <div class="px-6 pt-6 pb-5">
                <div class="flex items-baseline gap-2">
                    <span class="text-[11px] uppercase tracking-[0.18em] text-ignite font-display font-semibold">Helena</span>
                    <span class="font-display font-extrabold text-xl text-white">Quest</span>
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
                    ['label' => 'Auditoria',        'route' => 'admin.logs.index',         'icon' => 'log'],
                ];
            @endphp
            <nav class="flex-1 px-3 py-2 space-y-0.5" aria-label="Navegação principal">
                @foreach ($navigation as $item)
                    @php
                        $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route']));
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="nav-link relative flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-display font-medium {{ $active ? 'active' : 'text-gray-400' }}">
                        @php echo $item['label']; @endphp
                    </a>
                @endforeach
            </nav>
            <div class="px-4 py-4 border-t border-white/10 space-y-2">
                <div class="px-2">
                    <p class="text-sm text-white font-display font-semibold">{{ auth()->user()?->name ?? 'Admin' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()?->email ?? '—' }}</p>
                </div>
                <a href="{{ route('admin.settings') }}" class="nav-link flex items-center gap-2 px-2 py-2 rounded-lg text-sm text-gray-400 font-display font-medium">
                    Configurações
                </a>
                <form method="POST" action="{{ route('admin.logout') }}" class="px-2">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-white font-display transition-colors duration-150">
                        Sair da conta
                    </button>
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
