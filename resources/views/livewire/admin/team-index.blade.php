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
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                @if ($t->crest_path)
                                    <img src="@php echo asset('storage/' . $t->crest_path); @endphp" alt="" class="w-8 h-8 rounded-full object-cover border border-rule">
                                @else
                                    <span class="w-3 h-3 rounded-full shrink-0" style="background:@php echo $t->color_hex; @endphp"></span>
                                @endif
                                <span class="font-display font-semibold">@php echo $t->name; @endphp</span>
                            </div>
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
                            <div class="flex items-center justify-end gap-2">
                                <a href="@php echo route("admin.teams.edit", $t->id); @endphp" class="p-2 rounded-lg hover:bg-ignite/10 text-chalk hover:text-ignite transition-colors duration-150" title="Editar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button type="button" x-on:click="confirmDelete(@php echo $t->id; @endphp, '@php echo $t->name; @endphp')" class="p-2 rounded-lg hover:bg-red-50 text-chalk hover:text-red-600 transition-colors duration-150" title="Excluir">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
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

    <script>
        let pendingDeleteId = null;
        let pendingDeleteName = '';

        function confirmDelete(id, name) {
            pendingDeleteId = id;
            pendingDeleteName = name;
            document.getElementById('modal-bg').classList.remove('hidden');
            document.getElementById('modal-box').classList.remove('hidden');
            document.getElementById('modal-team-name').textContent = name;
            document.getElementById('modal-input').value = '';
            document.getElementById('modal-error').classList.add('hidden');
            document.getElementById('modal-confirm').disabled = true;
        }

        function closeModal() {
            document.getElementById('modal-bg').classList.add('hidden');
            document.getElementById('modal-box').classList.add('hidden');
            pendingDeleteId = null;
            pendingDeleteName = '';
        }

        function checkDeleteInput() {
            const input = document.getElementById('modal-input').value;
            const btn = document.getElementById('modal-confirm');
            if (input === pendingDeleteName) {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        function executeDelete() {
            if (!pendingDeleteId) return;
            Livewire.dispatch('deleteTeam', { teamId: pendingDeleteId });
            closeModal();
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('team-deleted', () => { closeModal(); });
        });
    </script>

    <div id="modal-bg" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-200" onclick="closeModal()"></div>
    <div id="modal-box" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-paper rounded-xl border border-rule shadow-card p-6 w-full max-w-md" onclick="event.stopPropagation()">
            <h3 class="font-display font-bold text-lg text-ink mb-2">Excluir equipe</h3>
            <p class="text-chalk text-sm mb-4">
                Digite <strong id="modal-team-name" class="text-ink"></strong> para confirmar a exclusão.
            </p>
            <input id="modal-input" type="text" oninput="checkDeleteInput()" placeholder="Nome da equipe" class="w-full px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none mb-4">
            <p id="modal-error" class="text-ember text-xs hidden">Nome não corresponde</p>
            <div class="flex justify-end gap-3">
                <button onclick="closeModal()" class="px-4 py-2 rounded-card border border-rule text-chalk font-display font-semibold">Cancelar</button>
                <button id="modal-confirm" onclick="executeDelete()" disabled class="px-4 py-2 rounded-card bg-red-600 text-white font-display font-semibold opacity-50 cursor-not-allowed">Excluir</button>
            </div>
        </div>
    </div>
</div>
