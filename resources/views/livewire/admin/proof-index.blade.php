<div>
    <header class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Listagem</p>
            <h1 class="font-display font-extrabold text-3xl text-ink">Provas</h1>
      </div>
        <a href="@php echo route("competitions.index"); @endphp" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold">Vincular a competição</a>
  </header>

    @if ($this->proofs->isEmpty())
        <p class="text-chalk text-sm py-8 text-center">Nenhuma prova cadastrada. Crie uma competição e adicione provas nela</p>
    @else
        <ul class="space-y-4">
            @foreach ($this->proofs as $p)
                <li class="bg-white rounded-card border border-rule p-5 shadow-card flex items-baseline gap-4">
                    <span class="w-10 h-10 rounded-card shrink-0" style="background:@php echo $p->color_hex ?? "#FF6600"; @endphp</span>
                    <div class="flex-1">
                        <p class="font-display font-bold text-ink text-lg">@php echo $p->name; @endphp</p>
                        <p class="text-chalk text-xs uppercase tracking-wider">Competição: @php echo $p->competition->name; @endphp (@php echo $p->competition->year; @endphp</p>
                        <p class="text-chalk text-sm mt-1">@php echo $p->stages_count; @endphp etapas · @php echo $p->status; @endphp</p>
                 </div>
                    <a href="@php echo route("proofs.edit", $p->id); @endphp" class="text-ignite font-display font-semibold">Editar</a>
               </li>
            @endforeach
      </ul>
    @endif
</div>