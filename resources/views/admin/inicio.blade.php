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
        @php($enLista = fn ($l) => '/admin?lista='.$l.'#clientes')
        <a class="dt-stat ad-link {{ $lista === 'todos' ? 'ad-on' : '' }}" href="{{ $enLista('todos') }}"><span>Clientes</span><b>{{ $fmt($m['clientes']) }}</b><small>Ver la lista con buscador</small></a>
        <a class="dt-stat ad-link {{ $lista === 'activos' ? 'ad-on' : '' }}" href="{{ $enLista('activos') }}"><span>Clientes activos</span><b style="color:var(--ok-text)">{{ $fmt($m['activos']) }}</b><small>Servicio vigente o en gracia</small></a>
        <a class="dt-stat ad-link {{ $lista === 'inactivos' ? 'ad-on' : '' }}" href="{{ $enLista('inactivos') }}"><span>Clientes inactivos</span><b style="color:var(--danger-text)">{{ $fmt($m['inactivos']) }}</b><small>Servicio en pausa (sin renovar)</small></a>
        <a class="dt-stat ad-link {{ $lista === 'sin_activar' ? 'ad-on' : '' }}" href="{{ $enLista('sin_activar') }}"><span>Sin activar su tarjeta</span><b>{{ $fmt($m['sinTarjeta']) }}</b><small>Cuentas sin tarjeta enlazada</small></a>
        <a class="dt-stat ad-link" href="/admin/sin-uso"><span>Sin usar tarjeta o enlace</span><b style="color:var(--warn-text)">{{ $fmt($m['sinUso']) }}</b><small>{{ $mesesSinUso }}+ meses sin visitas · enviar recordatorio</small></a>
        <a class="dt-stat ad-link" href="/admin/carritos#en-proceso"><span>En proceso de compra</span><b>{{ $fmt($m['enProceso']) }}</b><small>Pedidos de las últimas horas</small></a>
        <a class="dt-stat ad-link" href="/admin/carritos"><span>Carritos abandonados</span><b>{{ $fmt($m['abandonados']) }}</b><small>{{ $fmt($m['promosEnviadas']) }} promos enviadas · {{ $fmt($m['comprasConPromo']) }} compraron · enviar promoción</small></a>
        <a class="dt-stat ad-link" href="/admin/carritos#declinados"><span>Declinan el pago</span><b>{{ $fmt($m['declinados']) }}</b><small>Bold rechazó y no volvieron a pagar</small></a>
        <a class="dt-stat ad-link" href="/admin/soporte"><span>Chat de ayuda</span><b>{{ $fmt($m['chatsEnCola']) }}</b><small>en cola · {{ $fmt($m['chatsEnAtencion']) }} en atención</small></a>
        <a class="dt-stat ad-link" href="/admin/soporte#tickets"><span>Tickets abiertos</span><b>{{ $fmt($m['ticketsAbiertos']) }}</b><small>Esperan respuesta</small></a>
    </div>

    @include('admin._clientes')

    <section class="dt-panel" id="compras">
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

@endsection
