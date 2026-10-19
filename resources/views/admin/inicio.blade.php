@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Administrador</h1>
            <p class="dt-sub">Cómo va el negocio: compras, clientes, uso de las tarjetas, carritos y ayuda.</p>
        </div>
    </div>
    @include('admin._tabs')

    @php($fmt = fn ($n) => number_format((float) $n, 0, ',', '.'))
    <div class="ad-grid">
        <a class="dt-stat ad-link" href="#compras"><span>Compras realizadas</span><b>{{ $fmt($m['compras']) }}</b><small>{{ $fmt($m['comprasMes']) }} este mes</small></a>
        <div class="dt-stat"><span>Ventas</span>
            @forelse ($m['ventas'] as $moneda => $total)<b style="font-size:24px">${{ $fmt($total) }} <small>{{ $moneda }}</small></b>@empty<b>$0</b>@endforelse
        </div>
        <div class="dt-stat"><span>Clientes</span><b>{{ $fmt($m['clientes']) }}</b><small>{{ $fmt($m['sinTarjeta']) }} aún sin activar su tarjeta</small></div>
        <div class="dt-stat"><span>Clientes activos</span><b style="color:#4ade80">{{ $fmt($m['activos']) }}</b><small>Servicio vigente o en gracia</small></div>
        <div class="dt-stat"><span>Clientes inactivos</span><b style="color:#f87171">{{ $fmt($m['inactivos']) }}</b><small>Servicio en pausa (sin renovar)</small></div>
        <a class="dt-stat ad-link" href="#sin-uso"><span>Sin usar tarjeta o enlace</span><b style="color:#fbbf24">{{ $fmt($m['sinUso']) }}</b><small>{{ $mesesSinUso }} meses o más sin visitas</small></a>
        <a class="dt-stat ad-link" href="/admin/carritos#en-proceso"><span>En proceso de compra</span><b>{{ $fmt($m['enProceso']) }}</b><small>Pedidos de las últimas horas</small></a>
        <a class="dt-stat ad-link" href="/admin/carritos"><span>Carritos abandonados</span><b>{{ $fmt($m['abandonados']) }}</b><small>{{ $fmt($m['promosEnviadas']) }} promos enviadas · {{ $fmt($m['comprasConPromo']) }} compraron</small></a>
        <a class="dt-stat ad-link" href="/admin/carritos#declinados"><span>Declinan el pago</span><b>{{ $fmt($m['declinados']) }}</b><small>Bold rechazó y no volvieron a pagar</small></a>
        <a class="dt-stat ad-link" href="/admin/soporte"><span>Chat de ayuda</span><b>{{ $fmt($m['chatsEnCola']) }}</b><small>en cola · {{ $fmt($m['chatsEnAtencion']) }} en atención</small></a>
        <a class="dt-stat ad-link" href="/admin/soporte#tickets"><span>Tickets abiertos</span><b>{{ $fmt($m['ticketsAbiertos']) }}</b><small>Esperan respuesta</small></a>
    </div>

    <section class="dt-panel" id="compras" style="margin-top:18px">
        <div class="dt-panel-h"><h2>Últimas compras</h2></div>
        @if ($ultimasCompras->isEmpty())
            <p class="dt-sub">Aún no hay compras pagadas.</p>
        @else
            <div class="ad-tabla" role="table">
                @foreach ($ultimasCompras as $p)
                    <div class="ad-fila" role="row">
                        <span><b>{{ $p->nombre }}</b><small>{{ $p->correo }} · {{ $p->ciudad }}</small></span>
                        <span>{{ $p->codigo }}<small>{{ $p->acabadoNombre() }}@if ($p->chip) · código <a href="/admin/chips?buscar={{ $p->chip->codigo }}" style="font-family:ui-monospace,Consolas,monospace">{{ $p->chip->codigo }}</a>@endif</small></span>
                        <span style="text-align:right"><b>{{ $p->totalTexto() }}</b><small>{{ $p->pagado_en?->format('d/m/Y H:i') }}</small></span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="dt-panel" id="sin-uso">
        <div class="dt-panel-h"><h2>Clientes sin usar su tarjeta o enlace</h2><span class="dt-chip">{{ $mesesSinUso }}+ meses</span></div>
        @if ($sinUso->isEmpty())
            <p class="dt-sub">Todos los clientes han tenido visitas en los últimos {{ $mesesSinUso }} meses.</p>
        @else
            <div class="ad-tabla" role="table">
                @foreach ($sinUso as $u)
                    <div class="ad-fila" role="row">
                        <span><b>{{ $u['nombre'] }}</b><small>{{ $u['correo'] }}</small></span>
                        <span>Último uso<small>{{ $u['ultimoUso'] ?? 'Nunca' }}</small></span>
                        <span style="text-align:right">Cliente desde<small>{{ $u['desde'] }}</small></span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
