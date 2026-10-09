@extends('layouts.app')

@section('contenido')
    @php
        $fmt = fn ($n) => number_format($n, 0, ',', '.');
        $publico = $fichas[0] ?? null;
    @endphp

    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Inicio</h1>
            <p class="dt-sub">Resumen de tu tarjeta digital.</p>
        </div>
        @if($publico)
            <a href="/dashboard/perfiles" class="dt-btn">Ver mis perfiles</a>
        @endif
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:18px">
        <div class="dt-stat"><b>{{ $fmt($stats['vistas']) }}</b><span>Vistas de perfil</span></div>
        <div class="dt-stat"><b>{{ $fmt($stats['vcf']) }}</b><span>Contactos guardados</span></div>
        <div class="dt-stat"><b>{{ $fmt($stats['whatsapp']) }}</b><span>Chats de WhatsApp</span></div>
        <div class="dt-stat"><b>{{ $fmt($stats['pagos']) }}</b><span>Clics en pagos</span></div>
    </div>

    @if(!$publico)
        <section class="dt-panel" style="text-align:center;padding:48px 20px">
            <h2 style="margin:0 0 6px;font-size:18px">Aún no tienes un perfil digital</h2>
            <p class="dt-sub" style="max-width:440px;margin:0 auto 20px">Crea tu identidad en pocos pasos y prepárate para enlazar tu tarjeta física inteligente.</p>
            <a href="/editor" class="dt-btn pri" style="height:40px;padding:0 20px">Crear mi perfil</a>
        </section>
    @else
        <section class="dt-panel" x-data="{ p: @js($publico) }">
            <div class="dt-panel-h">
                <h2>Perfil que se ve en público</h2>
                <span class="dt-chip {{ $publico['tieneChip'] ? 'up' : '' }}">{{ $publico['tieneChip'] ? 'Vinculado a tu tarjeta NFC' : 'Sin tarjeta NFC vinculada' }}</span>
            </div>
            <div style="display:grid;grid-template-columns:minmax(0,300px) minmax(0,1fr);gap:24px;align-items:center" class="dt-inicio">
                <div class="dt-thumb" style="cursor:default"><x-mini-perfil var="p" /></div>
                <div style="min-width:0">
                    <h3 style="margin:0;font-size:20px">{{ $publico['nombre'] ?: 'Sin nombre' }}</h3>
                    <p class="dt-sub" style="margin:2px 0 14px">{{ $publico['nombrePerfil'] }}</p>
                    <p style="margin:0 0 16px;font-family:ui-monospace,Consolas,monospace;font-size:13px;overflow-wrap:anywhere">portal.dauntoque.com/{{ $publico['nickname'] }}</p>
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        <a href="/{{ $publico['nickname'] }}" target="_blank" class="dt-btn">Ver perfil público</a>
                        <a href="/editor?id={{ $publico['id'] }}" class="dt-btn pri">Editar perfil</a>
                    </div>
                </div>
            </div>
        </section>
        <style>@media (max-width:640px){ .dt-inicio { grid-template-columns:1fr !important; } }</style>
    @endif

    {{-- Celebración al vincular una tarjeta NFC por primera vez --}}
    @if(session('nfc_success'))
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
        <div x-data="{ abierto: true }" x-show="abierto" style="position:fixed;inset:0;z-index:9999;display:grid;place-items:center;padding:16px;background:rgba(0,0,0,.6)">
            <div class="dt-panel" style="max-width:440px;width:100%;text-align:center;padding:32px 24px">
                <h2 style="margin:0 0 8px;font-size:22px">¡Tarjeta activada!</h2>
                <p class="dt-sub" style="margin:0 0 22px">Tu tarjeta DaUnToque quedó vinculada a tu perfil público.</p>
                <button @click="abierto = false" class="dt-btn pri" style="width:100%;height:42px">Comenzar a usar mi panel</button>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const fin = Date.now() + 3000, base = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10000 };
                const azar = (a, b) => Math.random() * (b - a) + a;
                const t = setInterval(() => {
                    const resta = fin - Date.now(); if (resta <= 0) return clearInterval(t);
                    const n = 50 * (resta / 3000);
                    confetti({ ...base, particleCount: n, origin: { x: azar(0.1, 0.3), y: Math.random() - 0.2 } });
                    confetti({ ...base, particleCount: n, origin: { x: azar(0.7, 0.9), y: Math.random() - 0.2 } });
                }, 250);
            }, 500);
        </script>
    @endif
@endsection
