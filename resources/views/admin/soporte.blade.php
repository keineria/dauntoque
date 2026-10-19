@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Soporte</h1>
            <p class="dt-sub">Chat en vivo con clientes (por orden de llegada) y tickets. Esta pantalla se actualiza sola.</p>
        </div>
        <form method="POST" action="/admin/soporte/tomar">
            @csrf
            <button class="dt-btn pri" type="submit" @disabled($cola->isEmpty())>Atender al siguiente ({{ $cola->count() }} en cola)</button>
        </form>
    </div>
    @include('admin._tabs')

    <div x-data="{ firma: null }" x-init="
        const revisar = async () => {
            try {
                const r = await (await fetch('/admin/soporte/resumen', { headers: { Accept: 'application/json' } })).json();
                if (firma && r.firma !== firma) location.reload();
                firma = r.firma;
            } catch (e) {}
        };
        revisar(); setInterval(revisar, 8000);"></div>

    <section class="dt-panel">
        <div class="dt-panel-h"><h2>Cola de espera <span class="dt-chip">{{ $cola->count() }}</span></h2></div>
        @forelse ($cola as $i => $c)
            <a class="ad-fila ad-link" href="/admin/soporte/{{ $c->id }}">
                <span><b>{{ $i + 1 }}. {{ $c->user->name }}</b><small>{{ $c->asunto }}</small></span>
                <span>{{ $c->user->email }}</span>
                <span style="text-align:right">Espera desde<small>{{ $c->created_at->diffForHumans() }}</small></span>
            </a>
        @empty
            <p class="dt-sub">Nadie está esperando.</p>
        @endforelse
    </section>

    <section class="dt-panel">
        <div class="dt-panel-h"><h2>Chats en atención</h2></div>
        @forelse ($misChats as $c)
            <a class="ad-fila ad-link" href="/admin/soporte/{{ $c->id }}">
                <span><b>{{ $c->user->name }}</b><small>{{ $c->asunto }}</small></span>
                <span>Atiende<small>{{ $c->agente?->name ?? '—' }}</small></span>
                <span style="text-align:right">Último mensaje<small>{{ $c->ultimo_mensaje_en?->diffForHumans() }}</small></span>
            </a>
        @empty
            <p class="dt-sub">No hay chats abiertos.</p>
        @endforelse
    </section>

    <section class="dt-panel" id="tickets">
        <div class="dt-panel-h"><h2>Tickets abiertos <span class="dt-chip">{{ $tickets->count() }}</span></h2></div>
        @forelse ($tickets as $c)
            <a class="ad-fila ad-link" href="/admin/soporte/{{ $c->id }}">
                <span><b>{{ $c->asunto }}</b><small>{{ $c->user->name }} · {{ $c->user->email }}</small></span>
                <span>Abierto<small>{{ $c->created_at->format('d/m/Y H:i') }}</small></span>
                <span style="text-align:right">Último mensaje<small>{{ $c->ultimo_mensaje_en?->diffForHumans() }}</small></span>
            </a>
        @empty
            <p class="dt-sub">No hay tickets abiertos.</p>
        @endforelse
    </section>

    <section class="dt-panel">
        <div class="dt-panel-h"><h2>Cerradas recientes</h2></div>
        @forelse ($cerradas as $c)
            <a class="ad-fila ad-link" href="/admin/soporte/{{ $c->id }}">
                <span><b>{{ $c->asunto }}</b><small>{{ $c->user->name }}</small></span>
                <span>{{ $c->tipo === 'chat' ? 'Chat' : 'Ticket' }}</span>
                <span style="text-align:right">{{ $c->cerrada_en?->format('d/m/Y H:i') }}</span>
            </a>
        @empty
            <p class="dt-sub">Aún no hay conversaciones cerradas.</p>
        @endforelse
    </section>
@endsection
