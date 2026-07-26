<div>
    <header class="mb-8">
        <p class="text-ignite text-xs font-display font-semibold uppercase tracking-[0.18em] mb-1">Auditoria</p>
        <h1 class="font-display font-extrabold text-3xl text-ink">Logs de autenticacao</h1>
        <p class="text-chalk mt-2">Cada login, logout, sessao encerrada, troca de senha</p>
</header>

    <div class="bg-white rounded-card border border-rule p-4 mb-4 grid grid-cols-1 md:grid-cols-3 gap-3 shadow-card">
        <select wire:model.live="teamFilter" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            <option value="">Todas as equipes</option>
            @php $teamsListL = $this->teamsList; @endphp
            @foreach ($teamsListL as $t)
                <option value="@php echo $t->id; @endphp">@php echo $t->name; @endphp</option>
            @endforeach
    </select>
        <select wire:model.live="actionFilter" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            <option value="">Toda acao</option>
            @foreach (["login","logout","failed","session_killed","password_change"] as $a)
                <option value="@php echo $a; @endphp">@php echo $a; @endphp</option>
            @endforeach
    </select>
        <select wire:model.live="successFilter" class="px-3 py-2 rounded-card border border-rule focus:border-ignite outline-none">
            <option value="">Sucesso / falha</option>
            <option value="1">Sucesso</option>
            <option value="0">Falha</option>
    </select>
</div>

    @php $logs = $this->logs; @endphp
    <div class="bg-white rounded-card border border-rule shadow-card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-paper text-chalk">
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Data</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Equipe</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Acao</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">IP</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Dispositivo</th>
                    <th class="text-left p-3 font-display font-semibold text-xs uppercase tracking-wider">Sucesso</th>
     </tr>
           </thead>
            <tbody>
                @forelse ($logs as $log)
                    @php
                        $logTeam = $log->team;
                        $logFinalClass = "bg-paper text-chalk";
                        if ($log->action === "login") $logFinalClass = "bg-ignite/15 text-ember";
                        elseif ($log->action === "failed") $logFinalClass = "bg-red-50 text-red-700";
                        elseif ($log->action === "session_killed") $logFinalClass = "bg-chalk/15 text-chalk";
                    @endphp
                    <tr class="border-t border-rule hover:bg-paper/40">
                        <td class="p-3 font-mono text-xs">@php echo $log->created_at?->format("Y-m-d H:i:s"); @endphp</td>
                        <td class="p-3">@php echo $logTeam?->name ?? $log->username_attempted ?? "—"; @endphp</td>
                        <td class="p-3">
                            <span class="text-[10px] uppercase px-2 py-0.5 rounded-pill {{ $logFinalClass }}">@php echo $log->action; @endphp</span>
                </td>
                        <td class="p-3 font-mono text-xs">@php echo $log->ip ?? "—"; @endphp</td>
                        <td class="p-3 font-mono text-xs">@php echo substr((string)($log->device_id ?? "—"), 0, 16); @endphp</td>
                        <td class="p-3">
                            @if ($log->success)
                                <span class="text-ignite font-bold">OK</span>
                            @else
                                <span class="text-ember font-bold">FALHA</span>
                            @endif
               </td>
                 </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-chalk">Sem logs para os filtros selecionados</td>
                 </tr>
                @endforelse
</tbody>
</table>
        <div class="p-4">@php echo $logs->links(); @endphp</div>
</div>
</div>