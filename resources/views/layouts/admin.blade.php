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
                    ['label' => 'Telão (preview)',  'route' => 'telao.index',               'icon' => 'monitor', 'blank' => true],
                    ['label' => 'Auditoria',        'route' => 'admin.logs.index',         'icon' => 'log'],
                ];
                $icons = [
                    'home' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                    'trophy' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3h14M12 3v4m0 0a4 4 0 014 4v2a4 4 0 01-8 0V7a4 4 0 014-4zm-4 13h8l-1 4H9l-1-4z"/></svg>',
                    'map' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>',
                    'qr' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>',
                    'users' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                    'key' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>',
                    'monitor' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                    'log' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'settings' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                    'logout' => '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>',
                ];
            @endphp
            <nav class="flex-1 px-3 py-2 space-y-0.5" aria-label="Navegação principal">
                @foreach ($navigation as $item)
                    @php
                        $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route']));
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       {{ !empty($item['blank']) ? 'target="_blank" rel="noopener"' : '' }}
                       class="nav-link relative flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-display font-medium {{ $active ? 'active' : 'text-gray-400' }}">
                        @php echo $icons[$item['icon']]; @endphp
                        @php echo $item['label']; @endphp
                    </a>
                @endforeach
            </nav>
            <div class="px-4 py-4 border-t border-white/10 space-y-3">
                <div class="px-2">
                    <p class="text-sm text-white font-display font-semibold">{{ auth()->user()?->name ?? 'Admin' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()?->email ?? '—' }}</p>
                </div>
                <a href="{{ route('admin.settings') }}" class="nav-link flex items-center gap-3 px-2 py-2 rounded-lg text-sm text-gray-400 font-display font-medium">
                    @php echo $icons['settings']; @endphp
                    Configurações
                </a>
                <form method="POST" action="{{ route('admin.logout') }}" class="px-2">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-display font-semibold bg-red-500/10 text-red-400 hover:bg-red-500/20 hover:text-red-300 transition-colors duration-150 w-full">
                        @php echo $icons['logout']; @endphp
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
