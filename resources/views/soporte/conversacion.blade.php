@extends('layouts.app')

{{-- Chat o ticket: la misma pantalla para el cliente y para el agente. Los mensajes nuevos llegan cada pocos segundos. --}}
@php($base = $comoAgente ? '/admin/soporte/' . $c->id : '/dashboard/ayuda/' . $c->id)
@section('contenido')
    <div class="dt-head">
        <div>
            <a href="{{ $comoAgente ? '/admin/soporte' : '/dashboard/ayuda' }}" class="dt-sub" style="text-decoration:none">← {{ $comoAgente ? 'Soporte' : 'Ayuda' }}</a>
            <h1 class="dt-h1" style="font-size:24px;margin-top:6px">{{ $c->asunto }}</h1>
            <p class="dt-sub">
                {{ $c->tipo === 'chat' ? 'Chat en vivo' : 'Ticket' }} ·
                @if ($comoAgente){{ $c->user->name }} ({{ $c->user->email }}) · @endif
                abierto el {{ $c->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        @if ($c->estado !== 'cerrada')
            <form method="POST" action="{{ $base }}/cerrar" onsubmit="return confirm('¿Cerrar esta conversación?')">
                @csrf<button class="dt-btn" type="submit">{{ $comoAgente ? 'Cerrar conversación' : 'Terminar' }}</button>
            </form>
        @endif
    </div>

    <section class="dt-panel sp-chat" x-data="chatSoporte(@js($base), @js($comoAgente))" x-init="iniciar()">
        <div class="sp-aviso" x-show="estado === 'en_cola'" x-cloak>
            <b x-text="puesto === 1 ? 'Eres el siguiente en la fila' : 'Hay ' + (puesto - 1) + ' ' + (puesto - 1 === 1 ? 'persona' : 'personas') + ' antes que tú'"></b>
            <span>Un agente te atenderá en orden de llegada. Puedes dejar esta pantalla abierta o volver luego.</span>
        </div>
        <div class="sp-aviso" x-show="estado === 'abierta' && agente && !comoAgente && @js($c->tipo === 'chat')" x-cloak>
            <span>Te atiende <b x-text="agente"></b>.</span>
        </div>

        <div class="sp-mensajes" x-ref="lista" aria-live="polite">
            <template x-for="m in mensajes" :key="m.id">
                <div class="sp-msj" :class="m.agente === comoAgente ? 'mio' : ''">
                    <p x-text="m.texto"></p>
                    <small x-text="(m.agente ? (comoAgente ? 'Tú' : 'DaUnToque') : (comoAgente ? 'Cliente' : 'Tú')) + ' · ' + m.hora"></small>
                </div>
            </template>
        </div>

        <form class="sp-escribir" x-show="estado !== 'cerrada'" @submit.prevent="enviar()">
            <textarea class="dt-input" rows="2" x-model="texto" maxlength="3000" placeholder="Escribe tu mensaje…" @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); enviar(); }" aria-label="Mensaje"></textarea>
            <button class="dt-btn pri" type="submit" :disabled="enviando || !texto.trim()">Enviar</button>
        </form>
        <p class="dt-sub" x-show="estado === 'cerrada'" x-cloak style="text-align:center">Esta conversación está cerrada.</p>
        <p style="color:var(--danger-text);margin:8px 0 0;font-size:13px" x-show="error" x-text="error" x-cloak></p>
    </section>

    <script>
        function chatSoporte(base, comoAgente) {
            return {
                comoAgente, mensajes: [], estado: @js($c->estado), puesto: null, agente: null, texto: '', enviando: false, error: '', ultimo: 0, temporizador: null,
                iniciar() {
                    this.traer();
                    // Más seguido mientras el chat está vivo; los tickets cerrados no necesitan revisarse
                    this.temporizador = setInterval(() => { if (this.estado !== 'cerrada' && !document.hidden) this.traer(); }, 4000);
                },
                aplicar(r) {
                    this.estado = r.estado; this.puesto = r.puesto; this.agente = r.agente;
                    const nuevos = r.mensajes.filter(m => m.id > this.ultimo);
                    if (!nuevos.length) return;
                    this.mensajes.push(...nuevos);
                    this.ultimo = nuevos[nuevos.length - 1].id;
                    this.$nextTick(() => this.$refs.lista.scrollTop = this.$refs.lista.scrollHeight);
                },
                async traer() {
                    try {
                        const r = await fetch(`${base}/mensajes?desde=${this.ultimo}`, { headers: { Accept: 'application/json' } });
                        if (r.ok) this.aplicar(await r.json());
                    } catch (e) {}
                },
                async enviar() {
                    const texto = this.texto.trim();
                    if (!texto || this.enviando) return;
                    this.enviando = true; this.error = '';
                    try {
                        const r = await fetch(`${base}/mensajes`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({ texto, desde: this.ultimo }),
                        });
                        const datos = await r.json();
                        if (!r.ok) { this.error = datos.message || 'No se pudo enviar. Inténtalo de nuevo.'; return; }
                        this.texto = '';
                        this.aplicar(datos);
                    } catch (e) { this.error = 'Sin conexión con el servidor.'; }
                    finally { this.enviando = false; }
                },
            };
        }
    </script>
@endsection
