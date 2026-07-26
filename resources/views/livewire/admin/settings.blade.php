<div class="max-w-2xl">
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Configurações</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Preferências do sistema</h1>
        <p class="text-chalk mt-1">Configurações gerais da plataforma</p>
    </header>

    <div class="bg-white rounded-xl border border-rule p-6 space-y-6">
        <div>
            <h2 class="font-display font-semibold text-lg text-ink mb-1">Perfil</h2>
            <p class="text-sm text-chalk">@php echo auth()->user()->name; @endphp · @php echo auth()->user()->email; @endphp</p>
            <p class="text-xs text-chalk mt-1">Função: @php echo auth()->user()->role; @endphp</p>
        </div>

        <hr class="border-rule">

        <div>
            <h2 class="font-display font-semibold text-lg text-ink mb-1">Em desenvolvimento</h2>
            <p class="text-sm text-chalk">Em breve: alteração de senha, personalização do telão, preferências de notificação.</p>
        </div>
    </div>
</div>
