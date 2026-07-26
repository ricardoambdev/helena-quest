<div>
    <header class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Listagem</p>
            <h1 class="font-display font-extrabold text-3xl text-ink">Equipes</h1>
     </div>
        <a href="@php echo route("admin.teams.create"); @endphp" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold">Nova equipe</a>
 </header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-3 shadow-card">
        <input type="search" wire:model.live.debounce.250ms="search" placeholder="Buscar por nome ou usuario" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
        <select wire:model.live="competitionFilter" class="px-3 py-2 rounded-card border border-rule outline-none">
            <option value="">Todas as competicoes</option>
            @foreach ($this->competitionsList as $c)
                <option value="@php echo $c->id; @endphp">@php echo $c->name; @endphp</option>
            @endforeach
     </select>
        <select wire:model.live="statusFilter" class="px-3 py-2 rounded-card border border-rule outline-none">
            <option value="">Todos os status</option>
            @foreach (["active","blocked","inactive","eliminated"] as $s)
                <option value="@php echo $s; @endphp">@php echo $s; @endphp</option>
            @endforeach
     </select>
 </div>

    <div class="bg-white rounded-card border border-rule shadow-card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-paper text-chalk">
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Nome</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Usuario</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Status</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Competicao</th>
                    <th class="text-right p-3 font-display font-semibold text-xs uppercase tracking-wider">Acoes</th>
      </tr>
           </thead>
            <tbody>
                @forelse ($this->teams as $t)
                    <tr class="border-t border-rule hover:bg-paper/40">
                        <td class="p-3 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background:@php echo $t->color_hex; @endphp"></span>
                            <span class="font-display font-semibold">@php echo $t->name; @endphp</span>
                 </td>
                        <td class="p-3 font-mono text-sm text-chalk">@php echo $t->username; @endphp</td>
                        <td class="p-3">
                            <span class="text-[10px] uppercase px-2 py-0.5 rounded-pill
                            @switch($t->status)
                                @case("active") bg-ignite/15 text-ember @break
                                @case("blocked") bg-red-50 text-red-700 @break
                                @case("eliminated") bg-ink/15 text-ink @break
                                @default bg-chalk/10 text-chalk
                            @endswitch">@php echo $t->status; @endphp</span>
                 </td>
                        <td class="p-3 text-chalk text-sm">@php echo $t->competition?->name ?? "—"; @endphp</td>
                        <td class="p-3 text-right">
                            <a href="@php echo route("admin.teams.edit", $t->id); @endphp" class="text-ignite font-display font-semibold hover:underline">Editar</a>
                 </td>
                  </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-chalk">Nenhuma equipe encontrada</td>
                  </tr>
                @endforelse
       </tbody>
 </table>
        <div class="p-4">@php echo $this->teams->links(); @endphp</div>
</div>
</div>