@extends('layouts.admin')
@section('titulo', 'Rendimiento')

@section('contenido')
    @php
        $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
        $pct = fn ($n) => $n === null ? '—' : str_replace('.', ',', (string) $n) . ' %';
        $ventas = fn (array $v) => $v ? collect($v)->map(fn ($t, $m) => '$' . $fmt($t) . ' ' . $m)->implode(' · ') : '$0';
        $maxPersonas = max(1, max(array_column($serie, 'personas') ?: [0]));
        $paises = ['CO' => 'Colombia', 'US' => 'Estados Unidos', 'MX' => 'México', 'EC' => 'Ecuador', 'PE' => 'Perú', 'VE' => 'Venezuela', 'PA' => 'Panamá', 'ES' => 'España', 'CL' => 'Chile', 'AR' => 'Argentina'];
    @endphp
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Rendimiento</h1>
            <p class="dt-sub">Medición propia del sitio de ventas: de dónde llegan las personas y qué fuentes y campañas venden.</p>
        </div>
        <nav class="dt-seg ad-tabs" aria-label="Periodo" style="margin:0">
            @foreach ($periodos as $d => $nombre)
                <a href="/admin/rendimiento?dias={{ $d }}" class="{{ $dias === $d ? 'on' : '' }}" @if ($dias === $d) aria-current="page" @endif>{{ $nombre }}</a>
            @endforeach
        </nav>
    </div>

    <section class="dt-panel" aria-labelledby="embudo-h">
        <div class="dt-panel-h"><h2 id="embudo-h">Embudo</h2></div>
        <div class="ad-embudo">
            <div class="ad-paso"><span>Personas</span><b>{{ $fmt($totales['personas']) }}</b><small>{{ $fmt($totales['paginas']) }} páginas vistas</small></div>
            <div class="ad-paso"><span>Llegaron al pago</span><b>{{ $fmt($totales['pedidos']) }}</b><small>pedidos creados</small></div>
            <div class="ad-paso"><span>Compraron</span><b class="ad-ok">{{ $fmt($totales['pagados']) }}</b><small>conversión {{ $pct($totales['conversion']) }}</small></div>
            <div class="ad-paso"><span>Ventas</span><b style="font-size:20px">{{ $ventas($totales['ventas']) }}</b><small>compras pagadas del periodo</small></div>
        </div>

        <div class="ad-graf" role="img" aria-label="Personas por día y días con compras, {{ strtolower($periodos[$dias]) }}">
            @foreach ($serie as $d)
                <div title="{{ \Illuminate\Support\Carbon::parse($d['dia'])->format('d/m') }}: {{ $d['personas'] }} personas, {{ $d['pagados'] }} compras">
                    @if ($d['pagados'] > 0)<span class="c" style="height:6px"></span>@endif
                    <span class="p" style="height:{{ max($d['personas'] > 0 ? 3 : 0, round($d['personas'] * 100 / $maxPersonas)) }}%"></span>
                </div>
            @endforeach
        </div>
        <div class="ad-leyenda"><span><i style="background:var(--primary);opacity:.5"></i>Personas por día</span><span><i style="background:var(--ok-t)"></i>Día con compras</span><span>Pasa el cursor por una barra para ver el detalle.</span></div>
        @if ($totales['personas'] === 0)
            <p class="dt-sub" style="margin:14px 0 0">Aún no hay visitas medidas. Empiezan a contarse cuando este cambio esté en el servidor y el sitio de ventas se genere de nuevo (una sola vez).</p>
        @endif
    </section>

    <div class="ad-pila">
        <section class="dt-panel" aria-labelledby="fuentes-h">
            <div class="dt-panel-h"><h2 id="fuentes-h">Por fuente</h2><small class="dt-sub">organic = buscadores · social = redes sin pagar · paid_social / cpc = anuncios</small></div>
            @if ($fuentes->isEmpty())
                <p class="dt-sub" style="margin:0">Sin datos en este periodo.</p>
            @else
                <div class="ad-scroll"><table class="ad-t" style="min-width:640px">
                    <thead><tr><th scope="col">Fuente / medio</th><th scope="col">Personas</th><th scope="col">Llegaron al pago</th><th scope="col">Compras</th><th scope="col">Conversión</th><th scope="col">Ventas</th></tr></thead>
                    <tbody>
                        @foreach ($fuentes as $f)
                            <tr><td><b>{{ $f['fuente'] }}</b><small>{{ $f['medio'] }}</small></td><td>{{ $fmt($f['personas']) }}</td><td>{{ $fmt($f['pedidos']) }}</td><td>{{ $fmt($f['pagados']) }}</td><td>{{ $pct($f['conversion']) }}</td><td>{{ $ventas($f['ventas']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table></div>
            @endif
        </section>

        <section class="dt-panel" aria-labelledby="campanas-h">
            <div class="dt-panel-h"><h2 id="campanas-h">Por campaña</h2><a class="dt-btn" href="/admin/campanas">Crear campaña</a></div>
            @if ($campanas->isEmpty())
                <p class="dt-sub" style="margin:0">Sin campañas en este periodo. Crea una en «Campañas y UTM» y usa su enlace.</p>
            @else
                <div class="ad-scroll"><table class="ad-t" style="min-width:720px">
                    <thead><tr><th scope="col">Campaña</th><th scope="col">Personas</th><th scope="col">Compras</th><th scope="col">Ventas</th><th scope="col">Costo por compra</th><th scope="col">Retorno</th></tr></thead>
                    <tbody>
                        @foreach ($campanas as $f)
                            <tr>
                                <td><b>{{ $f['campana']?->nombre ?? $f['utm'] }}</b><small>{{ $f['campana'] ? $f['campana']->canalNombre() : 'UTM sin campaña creada' }} · <code>{{ $f['utm'] }}</code></small></td>
                                <td>{{ $fmt($f['personas']) }}</td>
                                <td>{{ $fmt($f['pagados']) }}<small>{{ $fmt($f['pedidos']) }} llegaron al pago</small></td>
                                <td>{{ $ventas($f['ventas']) }}</td>
                                <td>{{ $f['costoCompra'] !== null ? '$' . $fmt($f['costoCompra']) . ' ' . $f['campana']->moneda : '—' }}</td>
                                <td>{{ $f['roas'] !== null ? str_replace('.', ',', (string) $f['roas']) . ' x' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
            @endif
        </section>

        <div class="ad-dos">
            <section class="dt-panel" aria-labelledby="paginas-h">
                <div class="dt-panel-h"><h2 id="paginas-h">Páginas más vistas</h2></div>
                @forelse ($paginas as $p)
                    <div class="ad-fila" style="grid-template-columns:minmax(0,1fr) auto"><span><code style="font-size:12.5px">{{ $p->pagina }}</code></span><span style="text-align:right"><b>{{ $fmt($p->vistas) }}</b><small>{{ $fmt($p->personas) }} personas</small></span></div>
                @empty
                    <p class="dt-sub" style="margin:0">Sin datos en este periodo.</p>
                @endforelse
            </section>
            <section class="dt-panel" aria-labelledby="audiencia-h">
                <div class="dt-panel-h"><h2 id="audiencia-h">Audiencia</h2></div>
                <span class="dt-label">Dispositivo</span>
                @forelse ($audiencia['dispositivos'] as $a)
                    <div class="ad-fila" style="grid-template-columns:minmax(0,1fr) auto"><span>{{ ucfirst($a->nombre ?? 'sin dato') }}</span><span><b>{{ $fmt($a->personas) }}</b></span></div>
                @empty <p class="dt-sub" style="margin:0 0 12px">Sin datos.</p> @endforelse
                <span class="dt-label" style="margin-top:14px">País</span>
                @forelse ($audiencia['paises'] as $a)
                    <div class="ad-fila" style="grid-template-columns:minmax(0,1fr) auto"><span>{{ $a->nombre ? ($paises[$a->nombre] ?? $a->nombre) : 'Sin dato' }}</span><span><b>{{ $fmt($a->personas) }}</b></span></div>
                @empty <p class="dt-sub" style="margin:0">Sin datos.</p> @endforelse
            </section>
        </div>

        <section class="dt-panel" aria-labelledby="plataformas-h">
            <div class="dt-panel-h"><h2 id="plataformas-h">En cada plataforma</h2><a class="dt-btn" href="/admin/pixeles">Píxeles</a></div>
            <p class="dt-sub" style="margin:0 0 12px">Para el gasto, las impresiones y los clics de cada anuncio, abre la plataforma (con el píxel conectado, ahí también verás las compras):</p>
            <div class="ad-acciones">
                @foreach (['meta' => ['Meta Ads', 'https://adsmanager.facebook.com/'], 'ga4' => ['Google Analytics', 'https://analytics.google.com/'], 'gads' => ['Google Ads', 'https://ads.google.com/'], 'tiktok' => ['TikTok Ads', 'https://ads.tiktok.com/'], 'clarity' => ['Clarity', 'https://clarity.microsoft.com/']] as $clave => [$nombre, $url])
                    <a class="dt-btn" href="{{ $url }}" target="_blank" rel="noopener">{{ $nombre }} <span class="ad-chip {{ isset($pixeles[$clave]) ? 'ok' : 'neutro' }}" style="padding:1px 8px">{{ isset($pixeles[$clave]) ? 'conectado' : 'sin píxel' }}</span></a>
                @endforeach
            </div>
        </section>
    </div>
@endsection
