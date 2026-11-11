{{-- Lista «Listo para vender» (EstadoPagos::listoParaVender) --}}
<ul class="ad-lista">
    @foreach ($listo as $paso)
        <li class="{{ $paso['hecho'] ? 'hecho' : '' }}">
            <span class="ic" aria-hidden="true">{{ $paso['hecho'] ? '✓' : '' }}</span>
            <span><b style="font-weight:600">{{ $paso['texto'] }}</b><span class="sr-only">{{ $paso['hecho'] ? ' (listo)' : ' (pendiente)' }}</span><small>{{ $paso['ayuda'] }}</small></span>
            @if (! $paso['hecho'] && $paso['enlace'] && ! request()->is(ltrim($paso['enlace'], '/')))<a href="{{ $paso['enlace'] }}">Resolver</a>@endif
        </li>
    @endforeach
</ul>
