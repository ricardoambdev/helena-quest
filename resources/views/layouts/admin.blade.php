<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Helena Quest — Painel')</title>

    {{-- Tailwind v4 via CDN para dev — substituir por build Vite em produção quando o ambiente permitir (npm install bloqueado por ENOSPC) --}}
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
                    borderRadius: { card: '12px', pill: '9999px' },
                    boxShadow: {
                        card: '0 1px 0 0 rgba(255,102,0,0.06), 0 6px 14px -8px rgba(13,13,15,0.06)',
                        active: '0 0 0 2px #FF6600, 0 10px 20px -10px rgba(255,102,0,0.35)',
                    }
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

        .stage-ladder { counter-reset: ladder; }
        .stage-ladder > .step { counter-increment: ladder; position: relative; padding-left: 3.25rem; }
        .stage-ladder > .step::before {
            content: counter(ladder, decimal-leading-zero);
            position: absolute; left: 0; top: 0.25rem;
            width: 2.25rem; height: 2.25rem;
            display: flex; align-items: center; justify-content: center;
            border-radius: 9999px; background: #FF6600; color: white;
            font-family: 'Inter', sans-serif; font-weight: 700; font-size: 0.875rem;
            box-shadow: 0 0 0 4px #FAF8F5;
        }
        .stage-ladder > .step:not(:last-child)::after {
            content: ''; position: absolute; left: 1.09rem; top: 2.5rem; bottom: -0.5rem;
            width: 2px; background: #E0DCD3;
        }
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
  </style>
</head>

<body class="bg-paper text-ink antialiased">
    <div class="min-h-screen grid lg:grid-cols-[240px_1fr]">
        <aside class="bg-ink text-paper px-5 py-7 flex flex-col gap-7 sticky top-0 h-screen">
            <div class="flex items-baseline gap-2">
                <span class="text-[11px] uppercase tracking-[0.18em] text-ignite font-display">Helena</span>
                <span class="font-display font-extrabold text-2xl text-paper">Quest</span>
          </div>

            @php
                $navigation = [
                    ['label' => 'Painel geral',     'route' => 'dashboard',          'icon' => 'home'],
                    ['label' => 'Competições',      'route' => 'competitions.index', 'icon' => 'trophy'],
                    ['label' => 'Provas',           'route' => 'proofs.index',       'icon' => 'map'],
                    ['label' => 'Etapas',           'route' => 'stages.index',       'icon' => 'qr'],
                    ['label' => 'Equipes',          'route' => 'teams.index',        'icon' => 'users'],
                    ['label' => 'Enigma Final',     'route' => 'final-enigma.index', 'icon' => 'key'],
                    ['label' => 'Telão (preview)',  'route' => 'telao.index',        'icon' => 'monitor'],
                    ['label' => 'Auditoria / Logs', 'route' => 'logs.index',         'icon' => 'log'],
                ];
            @endphp

            <nav class="flex flex-col gap-1 text-sm" aria-label="Navegação principal">
                @foreach ($navigation as $item)
                    @php
                        $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route']));
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="group flex items-center gap-3 px-3 py-2 rounded-card font-display font-semibold transition-colors duration-200
                              {{ $active ? 'bg-ignite text-ink shadow-active' : 'text-chalk hover:text-paper hover:bg-white/5' }}"
                       @if ($active) aria-current="page" @endif>
                        <span class="w-2 h-2 rounded-full {{ $active ? 'bg-ink' : 'bg-chalk/60' }}</span>
                        {{ $item['label'] }}
                  </a>
                @endforeach
          </nav>

            <div class="mt-auto pt-4 border-t border-white/10 text-xs text-chalk">
                <p class="font-display font-semibold text-paper mb-1">{{ auth()->user()?->name ?? 'Admin' }}</p>
                <p>{{ auth()->user()?->email ?? '—' }}</p>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-ignite hover:text-flame font-display font-semibold">Sair</button>
              </form>
          </div>
      </aside>

        <main class="px-6 lg:px-10 py-8">
            @if (session('success'))
                <div role="status" class="mb-6 px-4 py-3 rounded-card bg-flame/15 border border-ignite/40 text-ink font-display font-semibold">
                    {{ session('success') }}
              </div>
            @endif
            @if (session('error'))
                <div role="alert" class="mb-6 px-4 py-3 rounded-card bg-red-50 border border-red-200 text-red-800">
                    {{ session('error') }}
              </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
      </main>
  </div>

    @livewireScripts
</body>
</html>
