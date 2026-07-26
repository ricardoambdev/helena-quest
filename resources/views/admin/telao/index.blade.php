@php $comps = $competitions ?? []; @endphp
<div class="bg-ink text-paper min-h-screen px-6 py-8 font-body">
    <header class="mb-8">
        <p class="text-ignite text-[11px] uppercase tracking-[0.18em] font-display font-semibold">Telao</p>
        <h1 class="font-display font-extrabold text-3xl">Selecione uma competicao</h1>
 </header>
    <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-w-5xl">
        @forelse ($comps as $c)
            @php $link = route("telao.show", $c->id); @endphp
            <li>
                <a href="@php echo $link; @endphp" class="block bg-white/5 hover:bg-ignite/15 border border-white/10 hover:border-ignite rounded-card p-5 transition-colors duration-200">
                    <p class="font-mono text-xs text-chalk">@php echo $c->year; @endphp</p>
                    <p class="font-display font-bold text-2xl mt-2">@php echo $c->name; @endphp</p>
                    <p class="text-chalk mt-1 text-sm uppercase tracking-wider">@php echo $c->status; @endphp</p>
</a>
          </li>
        @empty
            <li class="col-span-full text-chalk">Nenhuma competicao cadastrada</li>
        @endforelse
  </ul>
</div>
