<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — Helena Quest</title>

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
                    },
                    fontFamily: {
                        display: ['Inter', 'system-ui', 'sans-serif'],
                        body: ['Nunito', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
 </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:700,800|nunito:400,600,700" rel="stylesheet" />
    <style>
        body { font-family: 'Nunito', sans-serif; }
        input { outline: none; }
 </style>
</head>
<body class="bg-ink text-paper min-h-screen grid place-items-center px-4">
    <main class="w-full max-w-md">
        <div class="mb-8 text-center">
            <p class="text-ignite text-[11px] uppercase tracking-[0.18em] font-display font-semibold">Helena</p>
            <h1 class="font-display font-extrabold text-4xl">Quest</h1>
            <p class="text-chalk mt-2">Painel da organização</p>
     </div>

        <form method="POST" action="@php echo route('admin.login.attempt'); @endphp" class="bg-paper text-ink rounded-card p-8 space-y-5 shadow-card">
            @csrf

            <label class="block">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Email</span>
                <input type="email" name="email" required autofocus class="mt-1 w-full px-3 py-2 rounded-card border border-rule focus:border-ignite">
                @error('email')
                    <span class="text-ember text-xs">@php echo $message; @endphp</span>
                @enderror
         </label>

            <label class="block">
                <span class="font-display text-xs uppercase tracking-wider text-chalk">Senha</span>
                <input type="password" name="password" required class="mt-1 w-full px-3 py-2 rounded-card border border-rule focus:border-ignite">
                @error('password')
                    <span class="text-ember text-xs">@php echo $message; @endphp</span>
                @enderror
         </label>

            <button type="submit" class="w-full px-4 py-3 rounded-card bg-ignite text-paper font-display font-bold hover:bg-ember transition-colors duration-200">
                Entrar
         </button>

            <p class="text-xs text-chalk text-center pt-2">Acesso restrito à organização da gincana</p>
     </form>
 </main>
</body>
</html>
