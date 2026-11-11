@extends('layouts.admin')
@section('titulo', 'Pagos')

@section('contenido')
    @php
        $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
        $estadoChip = ['pagado' => 'ok', 'rechazado' => 'mal', 'pendiente' => 'aviso'];
        $estadoTexto = ['pagado' => 'Pagado', 'rechazado' => 'Rechazado', 'pendiente' => 'Pendiente'];
        $conexion = $bold['llave'] && $bold['secreta'] ? ($bold['ultimoPagoReal'] ? 'ok' : 'aviso') : 'mal';
    @endphp
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Pagos</h1>
            <p class="dt-sub">Si la conexión con la pasarela funciona, qué avisos llegan y cómo van los cobros.</p>
        </div>
    </div>

    <div class="ad-dos">
        <section class="dt-panel" aria-labelledby="bold-h">
            <div class="dt-panel-h">
                <h2 id="bold-h">Bold · Colombia (y USD mientras llega Stripe)</h2>
                <span class="ad-chip {{ $conexion }}">{{ ['ok' => 'Funcionando', 'aviso' => 'Falta el primer pago real', 'mal' => 'Incompleto'][$conexion] }}</span>
            </div>
            <dl style="display:grid;grid-template-columns:auto 1fr;gap:10px 16px;margin:0;font-size:13.5px">
                <dt class="dt-sub" style="margin:0">Llave de identidad</dt>
                <dd style="margin:0">@if ($bold['llave'])<span class="ad-chip ok">Puesta</span>@else<span class="ad-chip mal">Falta</span> <small class="dt-sub">BOLD_API_KEY en el .env</small>@endif</dd>
                <dt class="dt-sub" style="margin:0">Llave secreta</dt>
                <dd style="margin:0">@if ($bold['secreta'])<span class="ad-chip ok">Puesta</span>@else<span class="ad-chip aviso">Vacía (modo pruebas)</span> <small class="dt-sub">en producción, BOLD_SECRET_KEY</small>@endif</dd>
                <dt class="dt-sub" style="margin:0">Webhook</dt>
                <dd style="margin:0"><code class="ad-url" style="display:block;padding:8px 10px">{{ $bold['webhook'] }}</code></dd>
                <dt class="dt-sub" style="margin:0">Último aviso</dt>
                <dd style="margin:0">
                    @if ($bold['ultimoAviso'])
                        {{ $bold['ultimoAviso']->created_at->timezone('America/Bogota')->format('d/m/Y h:i a') }}
                        <small class="dt-sub" style="display:block">{{ $bold['ultimoAviso']->tipo ?: 'sin tipo' }} · {{ $bold['ultimoAviso']->resultado }}</small>
                    @else
                        <span class="dt-sub">Aún no llega ninguno desde que se publicó este módulo.</span>
                    @endif
                </dd>
                <dt class="dt-sub" style="margin:0">Último pago real</dt>
                <dd style="margin:0">{{ $bold['ultimoPagoReal'] ? $bold['ultimoPagoReal']->created_at->timezone('America/Bogota')->format('d/m/Y h:i a') . ' · ' . $bold['ultimoPagoReal']->resultado : 'Ninguno todavía' }}</dd>
            </dl>
            <form method="POST" action="/admin/pagos/probar-bold" class="ad-acciones" style="margin-top:16px" x-data="{ probando: false }" @submit="probando = true">
                @csrf
                <button type="submit" class="dt-btn pri" :disabled="probando" x-text="probando ? 'Probando…' : 'Probar conexión con Bold'">Probar conexión con Bold</button>
                <small class="dt-sub">Consulta tus medios de pago en Bold con la llave. No cobra nada.</small>
            </form>
            <details style="margin-top:14px">
                <summary style="cursor:pointer;font-weight:600;font-size:13px;color:var(--link)">Cómo pasar Bold a producción, paso a paso</summary>
                <ol class="dt-sub" style="font-size:13px;line-height:1.7;margin:10px 0 0;padding-left:20px">
                    <li>Entra a <b>panel.bold.co</b> → menú <b>Integraciones</b> → <b>Llaves de integración</b> → pestaña <b>Producción</b>. Copia la llave de identidad y la llave secreta (no las pegues en el chat).</li>
                    <li>En el servidor, abre el archivo <code>.env</code> del portal y cambia <code>BOLD_API_KEY</code> y <code>BOLD_SECRET_KEY</code> por las de producción. Luego corre <code>php artisan config:cache</code>.</li>
                    <li>En Bold → <b>Integraciones</b> → <b>Webhooks</b> → <b>Producción</b>, crea el webhook con la dirección de arriba y marca los eventos de venta aprobada y rechazada.</li>
                    <li>Vuelve aquí y toca <b>Probar conexión con Bold</b>.</li>
                    <li>Haz una compra real pequeña desde dauntoque.com. En «Últimos avisos» debe aparecer <b>SALE_APPROVED · pedido DT-…</b>. Después reembólsala desde el panel de Bold.</li>
                </ol>
            </details>
        </section>

        <section class="dt-panel" aria-labelledby="listo-h">
            <div class="dt-panel-h"><h2 id="listo-h">Listo para vender</h2></div>
            @include('admin._listo')
        </section>
    </div>

    <div class="ad-pila">
        <section class="dt-panel" aria-labelledby="cobros-h">
            <div class="dt-panel-h"><h2 id="cobros-h">Cobros de los últimos {{ $cobros['dias'] }} días</h2></div>
            <div class="ad-embudo">
                <div class="ad-paso"><span>Compras pagadas</span><b class="ad-ok">{{ $fmt($cobros['pagados']) }}</b><small>@forelse ($cobros['ventas'] as $moneda => $total)${{ $fmt($total) }} {{ $moneda }}@if (! $loop->last) · @endif @empty $0 @endforelse</small></div>
                <div class="ad-paso"><span>Pendientes</span><b class="ad-aviso">{{ $fmt($cobros['pendientes']) }}</b><small>abrieron el pago y no terminaron</small></div>
                <div class="ad-paso"><span>Rechazadas</span><b class="ad-mal">{{ $fmt($cobros['rechazados']) }}</b><small><a href="/admin/carritos#declinados" style="color:var(--link)">ver en Carritos</a></small></div>
                <div class="ad-paso"><span>Aprobación</span><b>{{ $cobros['aprobacion'] !== null ? $cobros['aprobacion'] . ' %' : '—' }}</b><small>pagadas sobre pagadas + rechazadas</small></div>
                <div class="ad-paso"><span>Renovaciones</span><b>{{ $fmt($cobros['renovacionesPagadas']) }}</b><small>{{ $fmt($cobros['renovacionesPendientes']) }} pendientes</small></div>
            </div>
        </section>

        <div class="ad-dos">
            <section class="dt-panel" aria-labelledby="pedidos-h">
                <div class="dt-panel-h"><h2 id="pedidos-h">Últimos pedidos</h2></div>
                @if ($cobros['ultimosPedidos']->isEmpty())
                    <p class="dt-sub" style="margin:0">Aún no hay pedidos.</p>
                @else
                    <div class="ad-tabla" role="table">
                        @foreach ($cobros['ultimosPedidos'] as $p)
                            <div class="ad-fila" role="row">
                                <span><b>{{ $p->codigo }}</b> · {{ $p->nombre }}<small>{{ $p->correo }}</small></span>
                                <span><span class="ad-chip {{ $estadoChip[$p->estado] ?? 'neutro' }}">{{ $estadoTexto[$p->estado] ?? $p->estado }}</span><small style="margin-top:4px">{{ $p->utm_source ? $p->utm_source . ($p->utm_campaign ? ' · ' . $p->utm_campaign : '') : 'directo' }}</small></span>
                                <span style="text-align:right"><b>${{ $fmt($p->monto) }} {{ $p->moneda }}</b><small>{{ $p->created_at->timezone('America/Bogota')->format('d/m/Y h:i a') }}</small></span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="dt-panel" aria-labelledby="avisos-h">
                <div class="dt-panel-h"><h2 id="avisos-h">Últimos avisos de Bold</h2></div>
                @if ($bold['avisos']->isEmpty())
                    <p class="dt-sub" style="margin:0">Cuando Bold avise un pago (o toques «Probar webhook» en su panel) aparece aquí, con la hora y a qué pedido correspondió.</p>
                @else
                    <div class="ad-tabla" role="table">
                        @foreach ($bold['avisos'] as $a)
                            @php($tono = $a->resultado === 'firma inválida' ? 'mal' : ($a->resultado === 'sin coincidencia' ? 'aviso' : 'ok'))
                            <div class="ad-fila" role="row" style="grid-template-columns:minmax(0,1.2fr) minmax(0,1.3fr) minmax(0,1fr)">
                                <span><b>{{ $a->tipo ?: 'sin tipo' }}</b><small>{{ $a->referencia ?: '—' }}</small></span>
                                <span><span class="ad-chip {{ $tono }}">{{ $a->resultado }}</span></span>
                                <span style="text-align:right"><small>{{ $a->created_at->timezone('America/Bogota')->format('d/m/Y h:i a') }}</small></span>
                            </div>
                        @endforeach
                    </div>
                    <p class="dt-sub" style="margin:12px 0 0;font-size:12.5px">«Sin coincidencia» es normal con el botón «Probar webhook» de Bold (manda datos de ejemplo). «Firma inválida» indica que la llave secreta del servidor no es la del webhook.</p>
                @endif
            </section>
        </div>

        <section class="dt-panel" aria-labelledby="stripe-h">
            <div class="dt-panel-h"><h2 id="stripe-h">Stripe · Estados Unidos</h2><span class="ad-chip neutro">Próximamente</span></div>
            <p class="dt-sub" style="margin:0">Se conecta cuando esté lista la LLC con Stripe Atlas. Mientras tanto, los clientes de Estados Unidos pagan en dólares con Bold. Cuando llegue, aparecerá aquí con el mismo estado de conexión, avisos y cobros.</p>
        </section>
    </div>
@endsection
