<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Telão — Helena Quest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{ignite:'#FF6600',ember:'#CC5200',flame:'#FF8533',ink:'#171A21',paper:'#FAF8F5',chalk:'#7A7468',rule:'#E0DCD3'},fontFamily:{display:['Inter','system-ui','sans-serif'],body:['Nunito','system-ui','sans-serif'],mono:['JetBrains Mono','monospace']},borderRadius:{card:'12px',pill:'9999px'}}}}</script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700,800,900|nunito:400,600,700|jetbrains-mono:400,500,700" rel="stylesheet" />
    <style>
        body { font-family: 'Nunito', system-ui, sans-serif; background: #171A21; color: #FAF8F5; }
        @media (prefers-reduced-motion: reduce) { *,::before,::after { animation-duration:0.01ms !important; transition-duration:0.01ms !important; } }
    </style>
</head>
<body class="min-h-screen p-8">
    <div class="max-w-6xl mx-auto">
        <header class="mb-10 border-b border-white/10 pb-4">
            <p class="text-ignite text-xs uppercase tracking-[0.18em] font-display font-semibold">Telão</p>
            <h1 class="font-display font-extrabold text-4xl mt-1">Selecione uma competição</h1>
        </header>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($competitions as $c)
                <a href="{{ route('telao.show', $c->id) }}"
                   class="block bg-white/5 hover:bg-ignite/15 border border-white/10 hover:border-ignite rounded-xl p-5 transition-colors duration-200">
                    <p class="font-mono text-xs text-chalk">{{ $c->year }}</p>
                    <p class="font-display font-bold text-2xl mt-2">{{ $c->name }}</p>
                    <p class="text-chalk mt-1 text-sm uppercase tracking-wider">{{ $c->status }}</p>
                </a>
            @empty
                <p class="col-span-full text-chalk">Nenhuma competição cadastrada</p>
            @endforelse
        </div>
    </div>
</body>
</html>
