@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Ayuda</h1>
            <p class="dt-sub">Habla con nuestro equipo por chat o déjanos un ticket y te respondemos por aquí.</p>
        </div>
    </div>

    <section class="dt-panel" x-data="{ tipo: 'chat' }">
        <div class="dt-panel-h"><h2>¿En qué te ayudamos?</h2></div>
        <form method="POST" action="/dashboard/ayuda" style="display:grid;gap:12px">
            @csrf
            @if ($errors->any())<p style="color:#f87171;margin:0">{{ $errors->first() }}</p>@endif
            <div class="dt-seg" role="group" aria-label="Tipo de ayuda" style="justify-self:start">
                <button type="button" :class="tipo === 'chat' ? 'on' : ''" @click="tipo = 'chat'">Chat con un agente</button>
                <button type="button" :class="tipo === 'ticket' ? 'on' : ''" @click="tipo = 'ticket'">Dejar un ticket</button>
            </div>
            <input type="hidden" name="tipo" :value="tipo">
            <p class="dt-sub" style="margin:0" x-text="tipo === 'chat' ? 'Entras a una fila y un agente te atiende en orden de llegada.' : 'Te respondemos por escrito; verás la respuesta aquí en Ayuda.'"></p>
            <div><span class="dt-label">Asunto</span><input class="dt-input" name="asunto" required maxlength="150" value="{{ old('asunto') }}" placeholder="Ej.: Mi tarjeta no abre el perfil"></div>
            <div><span class="dt-label">Cuéntanos</span><textarea class="dt-input" name="mensaje" rows="4" required maxlength="3000">{{ old('mensaje') }}</textarea></div>
            <div><button class="dt-btn pri" type="submit" x-text="tipo === 'chat' ? 'Hablar con un agente' : 'Enviar ticket'">Hablar con un agente</button></div>
        </form>
    </section>

    <section class="dt-panel">
        <div class="dt-panel-h"><h2>Tus conversaciones</h2></div>
        @forelse ($conversaciones as $c)
            <a class="ad-fila ad-link" href="/dashboard/ayuda/{{ $c->id }}">
                <span><b>{{ $c->asunto }}</b><small>{{ $c->tipo === 'chat' ? 'Chat' : 'Ticket' }} · {{ $c->created_at->format('d/m/Y') }}</small></span>
                <span>{{ ['en_cola' => 'En la fila', 'abierta' => 'Abierta', 'cerrada' => 'Cerrada'][$c->estado] ?? $c->estado }}</span>
                <span style="text-align:right"><small>{{ $c->ultimo_mensaje_en?->diffForHumans() }}</small></span>
            </a>
        @empty
            <p class="dt-sub">Aún no nos has escrito.</p>
        @endforelse
    </section>
@endsection
