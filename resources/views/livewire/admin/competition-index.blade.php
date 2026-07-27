<div>
    <header class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Listagem</p>
            <h1 class="font-display font-extrabold text-3xl text-ink">Competições</h1>
    </div>
        <a href="{{ route('admin.competitions.create') }}" class="px-5 py-2 rounded-card bg-ignite text-paper font-display font-bold hover:bg-ember transition-colors duration-200">
            Nova competição
    </a>
</header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-3 shadow-card">
        <input type="search" wire:model.live.debounce.250ms="search" placeholder="Buscar por nome..." class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
        <select wire:model.live="yearFilter" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            <option value="">Todos os anos</option>
            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}">@php echo $y; @endphp</option>
            @endfor
       </select>
        <select wire:model.live="statusFilter" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            <option value="">Todos os status</option>
            @foreach (['planning','published','ongoing','paused','finished','archived'] as $s)
                <option value="{{ $s }}">@php echo $s; @endphp</option>
            @endforeach
       </select>
        <button type="button" wire:click="resetFilters" class="px-3 py-2 rounded-card border border-rule text-chalk">Limpar filtros</button>
</div>

    <div class="bg-white rounded-card border border-rule shadow-card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-paper text-chalk">
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Nome</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Ano</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Status</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Provas</th>
                    <th class="text-right p-3 font-display font-semibold text-xs uppercase tracking-wider">Ações</th>
</tr>
        </thead>
         <tbody>
                @forelse ($competitions as $c)
                    <tr class="border-t border-rule hover:bg-paper/40">
                        <td class="p-3 font-display font-semibold text-ink">@php echo $c->name; @endphp</td>
                        <td class="p-3 font-mono text-chalk">@php echo $c->year; @endphp</td>
                        <td class="p-3">
                            @php
                                $css = match ($c->status) {
                                    'planning'  => 'bg-chalk/10 text-chalk',
                                    'published' => 'bg-flame/20 text-ember',
                                    'ongoing'   => 'bg-ignite/15 text-ember',
                                    'finished'  => 'bg-ink/10 text-ink',
                                    default     => 'bg-rule text-chalk',
                                };
                            @endphp
                            <span class="text-[10px] uppercase px-2 py-0.5 rounded-pill {{ $css }}">@php echo $c->status; @endphp</span>
                 </td>
                        <td class="p-3 text-ink">@php echo $c->stages_count; @endphp</td>
                        <td class="p-3 text-right">
                            <a href="{{ route('admin.competitions.edit', $c->id) }}" class="text-ignite font-display font-semibold hover:underline">Editar</a>
                            @if ($c->status === 'planning' || $c->status === 'archived')
                                <button type="button" wire:click="delete({{ $c->id }})" wire:confirm="Excluir esta competição?" class="ml-3 text-ember font-display font-semibold hover:underline">Excluir</button>
                            @endif
                 </td>
                   </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-chalk">Nenhuma competição corresponde aos filtros</td>
                   </tr>
                @endforelse
        </tbody>
  </table>

        <div class="p-4">{{ $competitions->links() }}</div>
 </div>
</div>
