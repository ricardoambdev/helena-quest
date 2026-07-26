<!DOCTYPE html>
<html lang="pt-BR" class="bg-ink">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1920">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Telao Helena Quest')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ignite: '#FF6600', ember: '#CC5200', flame: '#FF8533',
                        ink: '#171A21', paper: '#FAF8F5', chalk: '#7A7468', rule: '#E0DCD3',
                    },
                    fontFamily: {
                        display: ['Inter', 'system-ui', 'sans-serif'],
                        body: ['Nunito', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    borderRadius: { card: '12px', pill: '9999px' },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700,800,900|nunito:400,600,700|jetbrains-mono:400,500,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 1920px; height: 1080px; overflow: hidden; background: #0D0D0F; color: #FAF8F5; font-family: 'Nunito', system-ui, sans-serif; }
        @media (prefers-reduced-motion: reduce) { *,::before,::after { animation-duration:0.01ms !important; transition-duration:0.01ms !important; } }
        .carousel-fade { transition: opacity 0.8s ease-in-out; }
        .bar-fill { transition: width 0.6s ease-out; }
        .pulse-glow { animation: pulseGlow 2s ease-in-out infinite; }
        @keyframes pulseGlow { 0%,100% { box-shadow: 0 0 8px rgba(255,102,0,0.3); } 50% { box-shadow: 0 0 20px rgba(255,102,0,0.7); } }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,102,0,0.4); border-radius: 2px; }
        .grid-4x3 { display: grid; grid-template-columns: repeat(4, 1fr); grid-template-rows: repeat(3, 1fr); gap: 8px; }
        .zone-cell { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 0; overflow: hidden; transition: background 0.3s; }
        .zone-cell.active { border-color: rgba(255,102,0,0.3); }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot }}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.18.1/dist/echo.iife.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js" defer></script>
    @livewireScripts
</body>
</html>
