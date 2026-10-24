@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Carritos</h1>
            <p class="dt-sub">Personas que empezaron a comprar y no pagaron. A cada una le puedes mandar una última promoción, una sola vez.</p>
        </div>
    </div>
    @include('admin._tabs')

    @unless ($promoActiva)
        <p class="dt-panel" style="color:var(--warn-text);margin:0 0 18px">El correo de promoción está apagado. Enciéndelo en <a href="/admin/correos" style="color:inherit">Correos</a> para poder enviarlo.</p>
    @endunless

    <section class="dt-panel" x-data="{ abierta: {{ $errors->any() ? 'true' : 'false' }} }">
        <div class="dt-panel-h">
            <h2>Última promoción: {{ $descuento }} % de descuento</h2>
            <button type="button" class="dt-btn" @click="abierta = !abierta" x-text="abierta ? 'Cerrar' : 'Editar promoción'"></button>
        </div>
        <p class="dt-sub" style="margin:0">El correo lleva un enlace a /comprar con el descuento y sus datos ya escritos. Vale {{ $diasPromo }} días.</p>
        <form x-show="abierta" x-cloak method="POST" action="/admin/carritos/promo" style="display:grid;gap:12px;margin-top:16px">
            @csrf
            @if ($errors->any())<p style="color:var(--danger-text);margin:0">{{ $errors->first() }}</p>@endif
            <div><span class="dt-label">Descuento (%)</span><input class="dt-input" style="max-width:140px" type="number" name="promo_descuento" min="0" max="50" required value="{{ old('promo_descuento', $descuento) }}"></div>
            <div><span class="dt-label">Asunto del correo (opcional)</span><input class="dt-input" name="promo_asunto" maxlength="120" value="{{ old('promo_asunto', $promoAsunto) }}" placeholder="Tu tarjeta DaUnToque te espera con un {{ $descuento }} % de descuento"></div>
            <div><span class="dt-label">Mensaje (opcional)</span><textarea class="dt-input" name="promo_mensaje" rows="3" maxlength="1000" placeholder="Vimos que no terminaste tu compra. Te guardamos tu tarjeta con un descuento especial, solo por estos días.">{{ old('promo_mensaje', $promoMensaje) }}</textarea></div>
            <div><button class="dt-btn pri" type="submit">Guardar promoción</button></div>
        </form>
    </section>

    <section class="dt-panel">
        <div class="dt-panel-h">
            <h2>Carritos abandonados <span class="dt-chip">{{ $abandonados->total() }}</span></h2>
            @if ($abandonados->total() > 0 && $promoActiva)
                <form method="POST" action="/admin/carritos/enviar-todos" onsubmit="return confirm('¿Enviar la promoción a todos los que aún no la han recibido?')">
                    @csrf<button class="dt-btn pri" type="submit">Enviar a todos los que faltan</button>
                </form>
            @endif
        </div>
        @if ($abandonados->isEmpty())
            <p class="dt-sub">No hay carritos abandonados en los últimos 30 días.</p>
        @else
            <div class="ad-tabla">
                @foreach ($abandonados as $p)
                    <div class="ad-fila">
                        <span><b>{{ $p->nombre }}</b><small>{{ $p->correo }} · {{ $p->celular }}</small></span>
                        <span>{{ $p->acabadoNombre() }}<small>{{ $p->created_at->format('d/m/Y H:i') }} · {{ $p->ciudad }}</small></span>
                        <span style="text-align:right">
                            @if ($conPromo->has($p->correo))
                                <span class="dt-chip up">Promo enviada</span>
                            @elseif ($promoActiva)
                                <form method="POST" action="/admin/carritos/{{ $p->id }}/enviar" style="display:inline">@csrf<button class="dt-btn" type="submit">Enviar promoción</button></form>
                            @endif
                            @php($digitos = preg_replace('/\D/', '', $p->celular))
                            <a class="dt-btn" href="https://wa.me/{{ strlen($digitos) === 10 ? '57' . $digitos : $digitos }}" target="_blank" rel="noopener">WhatsApp</a>
                        </span>
                    </div>
                @endforeach
            </div>
            <div style="margin-top:12px">{{ $abandonados->links() }}</div>
        @endif
    </section>

    <section class="dt-panel" id="en-proceso">
        <div class="dt-panel-h"><h2>En proceso de compra <span class="dt-chip">últimas {{ $horas }} horas</span></h2></div>
        @forelse ($enProceso as $p)
            <div class="ad-fila"><span><b>{{ $p->nombre }}</b><small>{{ $p->correo }}</small></span><span>{{ $p->totalTexto() }}</span><span style="text-align:right">{{ $p->created_at->diffForHumans() }}</span></div>
        @empty
            <p class="dt-sub">Nadie está pagando en este momento.</p>
        @endforelse
    </section>

    <section class="dt-panel" id="declinados">
        <div class="dt-panel-h"><h2>Pagos declinados</h2></div>
        @forelse ($declinados as $p)
            <div class="ad-fila"><span><b>{{ $p->nombre }}</b><small>{{ $p->correo }} · {{ $p->celular }}</small></span><span>{{ $p->totalTexto() }}</span><span style="text-align:right">{{ $p->created_at->format('d/m/Y H:i') }}</span></div>
        @empty
            <p class="dt-sub">Ningún pago declinado en los últimos 30 días.</p>
        @endforelse
    </section>
@endsection
