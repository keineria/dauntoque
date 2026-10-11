@extends('sitio.layout')
@php
    $i = $t['inicio'];
    $d = $i['demo'];
    $pn = $i['panel'];
    $modo = $compraExterna ? 'whatsapp' : 'linea';
    $envio = $t['envio'][$mercado['code']] ?? reset($t['envio']);
    $medio1 = $medios[0] ?? 'WhatsApp';
    $medio2 = $medios[1] ?? null;
    $ext = $compraExterna ? 'target="_blank" rel="noopener"' : '';
    $casos = [
        // 'extra' = segunda foto (su trabajo) en miniatura sobre la principal
        ['foto' => 'caso-kathe', 'nombre' => 'Kathe Glam & Nails', 'url' => 'https://kathe.dauntoque.com'],
        ['foto' => 'caso-brey', 'extra' => 'caso-brey-cara', 'nombre' => 'Breynner Logistics', 'url' => 'https://brey.dauntoque.com'],
        ['foto' => 'caso-carlos', 'extra' => 'caso-carlos-obra', 'nombre' => 'Carlos Mendoza', 'url' => 'https://carlos.dauntoque.com'],
        ['foto' => 'caso-camilo', 'extra' => 'caso-camilo-corte', 'nombre' => 'Palacio Barbershop', 'url' => 'https://camilo.dauntoque.com'],
        ['foto' => 'caso-yasser', 'extra' => 'caso-yasser-extra', 'nombre' => 'Yasser Alvarado', 'url' => 'https://yass.dauntoque.com'],
    ];
    // Caras de la prueba social del héroe (las de los casos y una más)
    $caras = array_merge(array_column($casos, 'foto'), ['caso-angela']);
    // Valor por separado de cada cosa incluida: partes del precio anterior que suman exactamente ese precio
    $valores = [];
    if ($mercado['antes']) {
        $redondeo = $mercado['moneda'] === 'COP' ? 1000 : 1;
        foreach ([0.30, 0.36, 0.12] as $parte) {
            $valores[] = round($mercado['antes'] * $parte / $redondeo) * $redondeo;
        }
        $valores[] = $mercado['antes'] - array_sum($valores);
        $valores = array_map(fn ($v) => \App\Console\Commands\GenerarSitio::dinero($v, $mercado['moneda']), $valores);
    }
    $producto = [
        '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $i['producto'], 'brand' => ['@type' => 'Brand', 'name' => 'DaUnToque'],
        'description' => $x($i['descripcion']), 'image' => [$dominio . $base . '/media/og-' . $version['idioma'] . '.jpg'],
        'offers' => ['@type' => 'Offer', 'price' => $mercado['precio'], 'priceCurrency' => $mercado['moneda'], 'availability' => 'https://schema.org/InStock', 'url' => $canonica($codigo)],
    ];
    $como = ['@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => $i['viajeH'],
        'step' => array_map(fn ($p, $n) => ['@type' => 'HowToStep', 'position' => $n + 1, 'name' => $p[0], 'text' => $x($p[1], [':envio' => $envio])], $i['viaje'], array_keys($i['viaje']))];
@endphp

@section('titulo', $i['titulo'])
@section('descripcion', $x($i['descripcion']))
@section('precarga')
    <link rel="preload" as="image" href="{{ $base }}/media/caso-kathe-sm.webp">
@endsection
@section('schema')
    <script type="application/ld+json">{!! json_encode($producto, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <script type="application/ld+json">{!! json_encode($como, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endsection

@section('contenido')
    {{-- 1. Héroe: resultado en el titular, precio del país y la tarjeta tocando el celular --}}
    <section class="hero">
        <div class="wrap">
            <div class="hero-txt">
                @if ($mercado['ahorroTexto'])<span class="badge"><svg width="14" height="14" aria-hidden="true"><use href="#spark"/></svg>{{ $x($i['oferta']) }}</span>@endif
                <h1>{{ $i['h1a'] }} <em>{{ $i['h1b'] }}</em></h1>
                <p class="lead mut">{{ $i['sub'] }}</p>
                <div class="precio-hero" aria-label="{{ $mercado['precioTexto'] }} {{ $mercado['moneda'] }}">
                    <strong class="num">{{ $mercado['precioTexto'] }}</strong>
                    <small>{{ $mercado['moneda'] }}</small>
                    @if ($mercado['antesTexto'])<s class="price-old num">{{ $mercado['antesTexto'] }}</s>@endif
                </div>
                <div class="ctas" data-compra>
                    <a class="btn btn-pri" href="{{ $comprar() }}" {!! $ext !!}>{{ $i['cta'] }} <svg width="18" height="18" aria-hidden="true"><use href="#nfc"/></svg></a>
                    <a class="btn btn-sec" href="#pruebalo">{{ $i['ctaDemo'] }}</a>
                </div>
                <div class="garantias">
                    <span><svg width="16" height="16" aria-hidden="true"><use href="#truck"/></svg>{{ $envio }}</span>
                    <span><svg width="16" height="16" aria-hidden="true"><use href="#cal"/></svg>{{ $i['sinMensualidades'] }}</span>
                    <span><svg width="16" height="16" aria-hidden="true"><use href="#shield"/></svg>{{ $i['garantia30'] }}</span>
                    <span><svg width="16" height="16" aria-hidden="true"><use href="#spark"/></svg><b>{{ $i['regalo'][0] }}</b> {{ $i['regalo'][1] }}</span>
                </div>
                <div class="prueba">
                    <div class="caras">
                        @foreach ($caras as $cara)<img src="{{ $base }}/media/{{ $cara }}-sm.webp" alt="" width="40" height="40">@endforeach
                    </div>
                    <div><div class="estrellas" role="img" aria-label="{{ $i['estrellas'] }}">★★★★★</div><span class="mut">{{ $i['prueba'] }}</span></div>
                </div>
            </div>

            @php $e = $i['escena']; @endphp
            <div class="escena" role="img" aria-label="{{ $e['aria'] }}">
                <div class="etq e1"><svg width="16" height="16" aria-hidden="true"><use href="#nfc"/></svg>{{ $e['chip'] }} <b>NTAG215</b> · {{ $e['chipSub'] }}</div>
                <div class="ondas" aria-hidden="true"><i></i><i></i><i></i></div>
                <div class="tarjeta" aria-hidden="true">
                    <div class="t-top"><span>dauntoque</span><svg class="nfc-ic" width="22" height="22"><use href="#nfc"/></svg></div>
                    <div><div class="t-nom">Kathe Glam &amp; Nails</div><div class="t-sub">Matte Black</div></div>
                </div>
                <div class="tel" aria-hidden="true">
                    <div class="pantalla">
                        <div class="bloqueo"><b>9:41</b><span>{{ $e['bloqueo'] }}</span></div>
                        <div class="perfil-mini">
                            <img class="av" src="{{ $base }}/media/caso-kathe-sm.webp" alt="" width="74" height="74">
                            <div class="n">Kathe Glam &amp; Nails</div>
                            <div class="r">{{ $e['rol'] }}</div>
                            <div class="pbtn g"><svg width="16" height="16"><use href="#user"/></svg>{{ $e['guardar'] }}</div>
                            <div class="pbtn"><svg width="16" height="16"><use href="#wa"/></svg>WhatsApp</div>
                            <div class="pbtn"><svg width="16" height="16"><use href="#cash"/></svg>{{ $x($e['pagarCon'], [':medio' => $medio1]) }}</div>
                            <div class="pgrid"><i></i><i></i><i></i></div>
                        </div>
                        <div class="toast-tel"><i><svg width="16" height="16"><use href="#ok"/></svg></i>{{ $e['guardado'] }}</div>
                    </div>
                </div>
                <div class="etq e2"><svg width="16" height="16" aria-hidden="true"><use href="#ok"/></svg>{{ $e['apps'] }} · <b>{{ $e['appsN'] }}</b></div>
            </div>
        </div>
    </section>

    <nav class="indice" aria-label="{{ $i['indiceAria'] }}">
        <div class="wrap">
            @foreach ($i['indice'] as [$id, $ico, $txt])
                <a href="#{{ $id }}"><svg width="16" height="16" aria-hidden="true"><use href="#{{ $ico }}"/></svg>{{ $txt }}</a>
            @endforeach
        </div>
    </nav>

    {{-- 2. Pruébalo: la persona toca el perfil como cliente y ve el panel del dueño en vivo --}}
    <section class="sec light" id="pruebalo">
        <div class="wrap">
            <div class="head">
                <span class="eyebrow">{{ $d['eyebrow'] }}</span>
                <h2>{{ $d['h'] }}</h2>
                <p class="lead mut">{{ $d['p'] }}</p>
            </div>
            <div class="demo" data-demo data-ahora="{{ $d['ahora'] }}" data-hace="{{ $d['hace'] }}" data-abrio="{{ $d['abrio'] }}">
                <div class="demo-tel">
                    <div class="tel-toast" role="status" aria-live="polite"><i><svg width="16" height="16" aria-hidden="true"><use href="#ok"/></svg></i><span>{{ $d['listo'] }}</span></div>
                    <div class="pantalla">
                        <div class="dp-head">
                            <img class="av" src="{{ $base }}/media/caso-kathe-sm.webp" alt="{{ $d['foto'] }}" width="92" height="92" loading="lazy">
                            <h3>Kathe Glam &amp; Nails</h3>
                            <p>{{ $d['rol'] }}</p>
                        </div>
                        <div class="dp-body">
                            <button type="button" class="g" data-acc="contacto" data-kpi="con" data-ico="📇" data-aviso="{{ $d['acciones']['contacto'][0] }}" data-evento="{{ $d['acciones']['contacto'][1] }}"><svg width="18" height="18" aria-hidden="true"><use href="#user"/></svg>{{ $d['guardar'] }}</button>
                            <button type="button" data-acc="wa" data-kpi="wa" data-ico="💬" data-aviso="{{ $d['acciones']['wa'][0] }}" data-evento="{{ $d['acciones']['wa'][1] }}"><svg width="18" height="18" aria-hidden="true"><use href="#wa"/></svg>{{ $d['wa'] }}</button>
                            <div class="dp-row">
                                <button type="button" data-acc="llamar" data-ico="📞" data-aviso="{{ $d['acciones']['llamar'][0] }}" data-evento="{{ $d['acciones']['llamar'][1] }}"><svg width="18" height="18" aria-hidden="true"><use href="#phone"/></svg>{{ $d['llamar'] }}</button>
                                <button type="button" data-acc="ig" data-ico="📸" data-aviso="{{ $d['acciones']['ig'][0] }}" data-evento="{{ $d['acciones']['ig'][1] }}"><svg width="18" height="18" aria-hidden="true"><use href="#spark"/></svg>{{ $d['ig'] }}</button>
                                <button type="button" data-acc="compartir" data-ico="🔁" data-aviso="{{ $d['acciones']['compartir'][0] }}" data-evento="{{ $d['acciones']['compartir'][1] }}"><svg width="18" height="18" aria-hidden="true"><use href="#share"/></svg>{{ $d['compartir'] }}</button>
                            </div>
                            <div class="dp-lbl">{{ $d['trabajos'] }}</div>
                            <div class="dp-gal" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></div>
                            <div class="dp-lbl">{{ $d['pagame'] }}</div>
                            <div class="dp-pay">
                                @foreach (array_slice($medios, 0, 2) as $m)
                                    @php $dato = $m . ' ' . ($d['datos'][$m] ?? '@kathenails'); @endphp
                                    <button type="button" data-acc="pago" data-kpi="pag" data-ico="💵" data-copiar="{{ $dato }}" data-aviso="{{ $x($d['copiado'], [':dato' => $dato]) }}" data-evento="{{ $x($d['pagoEvento'], [':medio' => $m]) }}"><svg width="18" height="18" aria-hidden="true"><use href="#cash"/></svg>{{ $m }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="demo-der">
                    <div class="panel-vivo">
                        <div class="pv-head"><b>{{ $d['panel'] }}</b><span class="vivo">{{ $d['vivo'] }}</span></div>
                        <div class="pv-kpis">
                            @foreach (['vis' => 38, 'con' => 12, 'wa' => 9, 'pag' => 4] as $k => $n)
                                <div><b data-k="{{ $k }}">{{ $n }}</b><span>{{ $d['kpis'][$loop->index] }}</span></div>
                            @endforeach
                        </div>
                        <ul class="feed" aria-live="polite">
                            @foreach ($d['feed'] as [$ico, $txt, $hora])<li><i>{{ $ico }}</i><span>{{ $txt }}</span><time>{{ $hora }}</time></li>@endforeach
                        </ul>
                    </div>
                    <ul class="pistas">
                        @foreach ($d['pistas'] as [$b, $txt])
                            <li><svg width="18" height="18" aria-hidden="true"><use href="#ok"/></svg><span><b>{{ $b }}</b> {{ $x($txt) }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. Gancho: segundo perfil de regalo (ejecutivo y personal) --}}
    <section class="sec dos-sec" id="dos-perfiles">
        <div class="wrap dos-perfiles" data-dos>
            <div class="dp-texto">
                <span class="cinta">{{ $i['dosOferta'] }}</span>
                <div class="head" style="margin:14px 0 0">
                    <h2>{{ $i['dosH'] }}</h2>
                    <p class="lead mut">{{ $i['dosP'] }}</p>
                </div>
                <div class="dp-modos" role="group">
                    @foreach ($i['dosModos'] as $n => $modoDos)
                        <button type="button" data-modo="{{ $n === 0 ? 'ejec' : 'personal' }}" aria-pressed="{{ $n === 0 ? 'true' : 'false' }}"><small>{{ $modoDos[0] }}</small>{{ $modoDos[1] }}</button>
                    @endforeach
                </div>
                <div class="dp-pasion" role="group">
                    <small>{{ $i['dosPasionH'] }}</small>
                    @foreach ($i['dosPasiones'] as $clave => $pasion)
                        <button type="button" data-pasion="{{ $clave }}" aria-pressed="false">{{ $pasion['boton'] }}</button>
                    @endforeach
                </div>
            </div>
            @php $ej = $i['dosEjecutivo']; @endphp
            <div class="dp-telefono">
                <div class="dp-perfil ejec" data-vista="ejec">
                    <div class="dp-portada"><img src="{{ $base }}/media/dp-ejec-portada.webp" width="640" height="400" alt="" loading="lazy"></div>
                    <div class="dp-cuerpo">
                        <img class="dp-avatar" src="{{ $base }}/media/dp-ejec-avatar.webp" width="220" height="220" alt="{{ $ej['nombre'] }}" loading="lazy">
                        <b class="dp-nombre">{{ $ej['nombre'] }} <i class="dp-ok" aria-hidden="true">✓</i></b>
                        <span class="dp-rol">{{ $ej['rol'] }}</span>
                        <div class="dp-cifras">@foreach ($ej['cifras'] as $c)<div><b>{{ $c[0] }}</b><span>{{ $c[1] }}</span></div>@endforeach</div>
                        <span class="dp-b pri">{{ $ej['botones'][0] }}</span>
                        <div class="dp-fila"><span class="dp-b">{{ $ej['botones'][1] }}</span><span class="dp-b">{{ $ej['botones'][2] }}</span></div>
                        <span class="dp-lbl">{{ $ej['galeria'] }}</span>
                        <div class="dp-g3">@for ($f = 1; $f <= 3; $f++)<img src="{{ $base }}/media/dp-ejec-{{ $f }}.webp" width="240" height="240" alt="" loading="lazy">@endfor</div>
                    </div>
                </div>
                @foreach ($i['dosPasiones'] as $clave => $pasion)
                    <div class="dp-perfil pers {{ $clave }}" data-vista="{{ $clave }}" hidden>
                        <div class="dp-heroe">
                            <img src="{{ $base }}/media/dp-{{ $clave }}-portada.webp" width="640" height="760" alt="{{ $pasion['alt'] }}" loading="lazy">
                            <div class="dp-txt"><span class="dp-kicker">{{ $i['dosFinde'] }}</span><b class="dp-nombre">{{ $pasion['nombre'] }}</b><span class="dp-rol">{{ $pasion['lema'] }}</span></div>
                        </div>
                        <div class="dp-cuerpo">
                            <div class="dp-tags">@foreach ($pasion['tags'] as $etiqueta)<span>{{ $etiqueta }}</span>@endforeach</div>
                            <div class="dp-fila"><span class="dp-b pri">{{ $pasion['cta'] }}</span><span class="dp-b">Instagram</span></div>
                            <div class="dp-g3">@for ($f = 1; $f <= 3; $f++)<img src="{{ $base }}/media/dp-{{ $clave }}-{{ $f }}.webp" width="240" height="240" alt="" loading="lazy">@endfor</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <ul class="dp-puntos">@foreach ($i['dosPuntos'] as $punto)<li>{{ $punto }}</li>@endforeach</ul>
        </div>
    </section>

    {{-- 3b. Tarjeta con IA: la IA escribe la presentación y el dueño personaliza todo --}}
    @php $ia = $i['ia']; @endphp
    <section class="sec light ia-sec" id="ia">
        <div class="wrap ia-wrap">
            <div class="ia-txt">
                <div class="head" style="margin:0">
                    <span class="eyebrow">{{ $ia['eyebrow'] }}</span>
                    <h2>{{ $ia['h'] }}</h2>
                    <p class="lead mut">{{ $ia['p'] }}</p>
                </div>
                <ol class="ia-pasos">
                    @foreach ($ia['pasos'] as $n => [$h, $txt])
                        <li><span class="ia-n">{{ $n + 1 }}</span><div><h3>{{ $h }}</h3><p class="mut">{{ $txt }}</p></div></li>
                    @endforeach
                </ol>
                <p class="ia-pronto"><svg width="16" height="16" aria-hidden="true"><use href="#spark"/></svg>{{ $ia['pronto'] }}</p>
            </div>
            <div class="ia-editor" role="img" aria-label="{{ $ia['aria'] }}">
                <div class="ia-barra" aria-hidden="true"><i></i><i></i><i></i><span>{{ $ia['editor'] }}</span></div>
                <div class="ia-cuerpo" aria-hidden="true">
                    <div class="ia-msg tu"><small>{{ $ia['tu'] }}</small>{{ $ia['pide'] }}</div>
                    <div class="ia-msg bot"><small><svg width="12" height="12"><use href="#spark"/></svg>{{ $ia['ia'] }}</small>{{ $ia['responde'] }}<span class="ia-usar"><svg width="14" height="14"><use href="#ok"/></svg>{{ $ia['usar'] }}</span></div>
                    <div class="ia-op">
                        <small>{{ $ia['diseno'] }}</small>
                        <div class="ia-temas">@foreach (['#0d0f10', '#f5f4ef', '#1e3a5f', '#2fae6e', '#c9a227', '#7c3aed', '#e11d48', '#0ea5e9', '#f28c66', '#334155'] as $n => $color)<i class="{{ $n === 0 ? 'on' : '' }}" style="background:{{ $color }}"></i>@endforeach</div>
                    </div>
                    <div class="ia-op">
                        <small>{{ $ia['botones'] }}</small>
                        <div class="ia-formas">@foreach ($ia['formas'] as $n => $forma)<span class="f{{ $n }} {{ $n === 0 ? 'on' : '' }}">{{ $forma }}</span>@endforeach</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. Tu panel: quién te vio, te guardó y te pagó --}}
    <section class="sec panel-sec" id="panel">
        <div class="wrap">
            <div class="head">
                <span class="eyebrow">{{ $pn['eyebrow'] }}</span>
                <h2>{{ $pn['h'] }}</h2>
                <p class="lead mut">{{ $pn['p'] }}</p>
            </div>
            <div class="dash" role="img" aria-label="{{ $pn['aria'] }}">
                <div class="dash-side" aria-hidden="true">
                    <span class="logo"><i></i><span>dauntoque</span></span>
                    @foreach ($pn['menu'] as $n => $op)
                        <span class="dash-op {{ $n === 3 ? 'on' : '' }}"><svg width="16" height="16"><use href="#{{ ['chart', 'nfc', 'user', 'chart', 'cash'][$n] }}"/></svg>{{ $op }}</span>
                    @endforeach
                </div>
                <div class="dash-main">
                    <div class="mini-kpis">@foreach ($pn['kpis'] as [$n, $txt])<div><b>{{ $n }}</b><span>{{ $txt }}</span></div>@endforeach</div>
                    <div class="dash-grid">
                        <div class="card-d">
                            <div class="card-top"><h3>{{ $pn['contactosH'] }}</h3><span class="chip-uid">{{ $pn['hoy'] }}</span></div>
                            <ul class="contactos">
                                @foreach ($pn['contactos'] as [$ini, $nom, $acc, $hora])
                                    <li><i>{{ $ini }}</i><span><b>{{ $nom }}</b><small>{{ $x($acc, [':medio' => $medio1]) }}</small></span><time>{{ $hora }}</time></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-d">
                            <div class="card-top"><h3>{{ $pn['visitasH'] }}</h3><span class="chip-uid">{{ $pn['visitasDelta'] }}</span></div>
                            <div class="barras">@foreach ([32, 45, 38, 60, 52, 28, 22, 48, 66, 58, 74, 69, 41, 88] as $h)<i style="height:{{ $h }}%"></i>@endforeach</div>
                            <div class="ejes">@foreach ($pn['ejes'] as $eje)<span>{{ $eje }}</span>@endforeach</div>
                            <p class="mut" style="font-size:13.5px;margin-top:14px">{{ $pn['ejemplo'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="notas-panel">@foreach ($pn['notas'] as [$b, $txt])<div><b>{{ $b }}</b><p>{{ $txt }}</p></div>@endforeach</div>
            @php $pia = $pn['ia']; @endphp
            <div class="ia-resumen">
                <div class="ia-resumen-txt">
                    <div class="ia-resumen-top"><span class="ia-chip"><svg width="14" height="14" aria-hidden="true"><use href="#spark"/></svg>{{ $pia['chip'] }}</span><span class="app-pronto">{{ $pia['pronto'] }}</span></div>
                    <h3>{{ $pia['h'] }}</h3>
                    <p class="mut">{{ $pia['p'] }}</p>
                </div>
                <ul class="ia-resumen-lista">
                    @foreach ($pia['puntos'] as [$ico, $txt])
                        <li><span class="app-ic"><svg width="18" height="18" aria-hidden="true"><use href="#{{ $ico }}"/></svg></span>{{ $txt }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- 4b. Apps adicionales que se activan dentro de la misma tarjeta (próximamente) --}}
    @php $ap = $i['apps']; @endphp
    <section class="sec apps-sec" id="apps">
        <div class="wrap">
            <div class="head">
                <span class="eyebrow">{{ $ap['eyebrow'] }}</span>
                <h2>{{ $ap['h'] }}</h2>
                <p class="lead mut">{{ $ap['p'] }}</p>
            </div>
            <div class="apps">
                @foreach ($ap['lista'] as $n => [$ico, $h, $txt])
                    <article class="app {{ $n === 0 ? 'destacada' : '' }}">
                        <div class="app-top"><span class="app-ic"><svg width="22" height="22" aria-hidden="true"><use href="#{{ $ico }}"/></svg></span><span class="app-pronto">{{ $ap['pronto'] }}</span></div>
                        <h3>{{ $h }}</h3>
                        <p class="mut">{{ $txt }}</p>
                    </article>
                @endforeach
            </div>
            <p class="apps-nota mut"><svg width="16" height="16" aria-hidden="true"><use href="#ok"/></svg>{{ $ap['nota'] }}</p>
        </div>
    </section>

    {{-- 5. De la compra al primer toque --}}
    <section class="sec light" id="viaje">
        <div class="wrap viaje-wrap">
            <div class="viaje-top">
                <div class="head" style="margin:0">
                    <span class="eyebrow">{{ $i['viajeEyebrow'] }}</span>
                    <h2>{{ $i['viajeH'] }}</h2>
                </div>
                <div class="marco-video" data-video="{{ $base }}/media/tienda">
                    <img class="portada" src="{{ $base }}/media/tienda-sm.webp" width="640" height="360" alt="{{ $i['viajeVideo'] }}" loading="lazy">
                </div>
            </div>
            <ol class="viaje">
                @foreach ($i['viaje'] as $n => [$h, $txt])
                    <li @if ($n === 3) class="clave" @endif>
                        <span class="paso">{{ $x($i['paso'], [':n' => $n + 1]) }}</span>
                        <span class="ic"><svg width="20" height="20" aria-hidden="true"><use href="#{{ ['cash', 'truck', 'nfc', 'user', 'chart'][$n] }}"/></svg></span>
                        <h3>{{ $h }}</h3><p>{{ $x($txt, [':envio' => $envio]) }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- 6. Tu tarjeta con tu nombre: acabado Matte Black o Crystal --}}
    @php $ac = $i['acab']; @endphp
    <section class="sec light" id="acabados" style="padding-top:0">
        <div class="wrap acabados" data-estudio data-vacio="{{ $ac['tuNombre'] }}">
            <div class="estudio">
                <div class="t3d">
                    <div class="fila"><span>dauntoque</span><svg class="nfc-ic" aria-hidden="true"><use href="#nfc"/></svg></div>
                    <div><div class="nom" data-nombre>{{ $ac['tuNombre'] }}</div><div class="car"><span data-car>Matte Black</span> · NFC</div></div>
                </div>
            </div>
            <div class="controles">
                <div class="head" style="margin:0">
                    <span class="eyebrow">{{ $ac['eyebrow'] }}</span>
                    <h2>{{ $ac['h'] }}</h2>
                </div>
                <div class="seg" role="group" aria-label="{{ $ac['aria'] }}">
                    @foreach (['matte-black', 'crystal'] as $a)
                        <button type="button" data-acab="{{ $a }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $t['acabados'][$a]['nombre'] }}</button>
                    @endforeach
                </div>
                <div class="campo">
                    <label for="nomInput">{{ $ac['label'] }}</label>
                    <input id="nomInput" maxlength="28" placeholder="{{ $ac['placeholder'] }}" autocomplete="off">
                </div>
                @foreach (['matte-black', 'crystal'] as $a)
                    @php $acabadoTxt = $t['acabados'][$a]; @endphp
                    <div class="acab-txt" data-acab-txt="{{ $a }}" @if (!$loop->first) hidden @endif>
                        <b>{{ $acabadoTxt['nombre'] }}</b>
                        <p class="mut">{{ $acabadoTxt['texto'] }} {{ $acabadoTxt['corto'] }}</p>
                        <div class="ctas" data-compra>
                            <a class="btn btn-pri" href="{{ $comprar($a, $x($t['waAcabado'], [':acabado' => $acabadoTxt['nombre']])) }}" {!! $ext !!}>{{ $ac['cta'] }}</a>
                            <a class="btn btn-sec" href="{{ $ir($codigo, 'tarjeta', $a) }}">{{ $ac['ver'] }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 7. Casos reales con su perfil vivo --}}
    <section class="sec" id="casos">
        <div class="wrap">
            <div class="head">
                <span class="eyebrow">{{ $i['casosEyebrow'] }}</span>
                <h2>{{ $i['casosH'] }}</h2>
            </div>
            {{-- Carrusel: 4 por vista, rota solo en bucle y se detiene con el mouse o al tocarlo (ver sitio.js) --}}
            <div class="casos" data-carrusel tabindex="0" aria-label="{{ $i['casosH'] }}">
                @foreach ($casos as $n => $c)
                    @php [$dato, $cita, $rol] = $i['casos'][$n]; @endphp
                    <article class="caso">
                        <div class="caso-fotos">
                            <img src="{{ $base }}/media/{{ $c['foto'] }}.webp" width="480" height="480" alt="{{ $c['nombre'] }}" loading="lazy">
                            @isset($c['extra'])<img class="caso-extra" src="{{ $base }}/media/{{ $c['extra'] }}.webp" width="160" height="160" alt="" loading="lazy">@endisset
                        </div>
                        <div>
                            <span class="dato">{{ $dato }}</span>
                            <blockquote>“{{ $cita }}”</blockquote>
                            <div class="quien"><span><b>{{ $c['nombre'] }}</b><span class="mut">{{ $rol }}</span></span><a href="{{ $c['url'] }}" target="_blank" rel="noopener">{{ $i['verPerfil'] }} →</a></div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 8. Comparación y calculadora --}}
    <section class="sec light" id="compara">
        <div class="wrap">
            <div class="head">
                <span class="eyebrow">{{ $i['comparaEyebrow'] }}</span>
                <h2>{{ $i['comparaH'] }}</h2>
            </div>
            <div class="tabla-wrap">
                <table>
                    <thead><tr>@foreach ($i['comparaCols'] as $col)<th scope="col" @if ($loop->last) class="col-dt" @endif>@if ($loop->first)<span class="sr">{{ $col }}</span>@else{{ $col }}@endif</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach ($i['comparaFilas'] as $fila)
                            <tr><th scope="row">{{ $fila[0] }}</th>
                                @for ($k = 1; $k <= 3; $k++)<td class="{{ $k === 3 ? 'col-dt' : ($fila[$k] === $i['comparaNo'] ? 'no' : '') }}">{{ $fila[$k] }}</td>@endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="calc" data-calc data-locale="{{ $version['hreflang'] }}" data-moneda="{{ $mercado['moneda'] }}">
                <div class="campo"><label for="cTar">{{ $i['calcTarjetas'] }}</label><input id="cTar" type="number" name="tarjetas" value="100" min="0" step="10" inputmode="numeric"></div>
                <div class="campo"><label for="cCos">{{ $i['calcCosto'] }} ({{ $mercado['moneda'] }})</label><input id="cCos" type="number" name="costo" value="{{ $mercado['moneda'] === 'COP' ? 40000 : 25 }}" min="0" inputmode="numeric"></div>
                <div class="r papel"><span>{{ $i['calcAnio'] }}</span><b class="num" data-anio>—</b></div>
                <div class="r dt"><span>{{ $i['calcVs'] }}</span><b class="num">{{ $mercado['precioTexto'] }}</b></div>
            </div>
        </div>
    </section>

    {{-- 9. Precio: valor apilado, botón de compra del país y garantía --}}
    <section class="sec" id="precio">
        <div class="wrap precio">
            <div class="oferta">
                <div class="tope">
                    <h3>{{ $i['ofertaH'] }}</h3>
                    @if ($mercado['ahorroTexto'])<span class="badge">{{ $x($i['ahorroCorto']) }}</span>@endif
                </div>
                <div class="monto"><strong class="num">{{ $mercado['precioTexto'] }}</strong><span class="mut">{{ $mercado['moneda'] }}</span>@if ($mercado['antesTexto'])<s class="price-old num">{{ $mercado['antesTexto'] }}</s>@endif</div>
                <ul class="stack">
                    @foreach ($i['stack'] as $n => $item)
                        <li><span><svg width="16" height="16" aria-hidden="true"><use href="#ok"/></svg>{{ $x($item) }}</span>@if ($valores)<em>{{ $valores[$n] }}</em>@endif</li>
                    @endforeach
                    <li><span><svg width="16" height="16" aria-hidden="true"><use href="#ok"/></svg><span><b>{{ $i['stackRegalo'][0] }}</b> · {{ $i['stackRegalo'][1] }}</span></span><em>{{ $i['stackRegalo'][2] }}</em></li>
                    <li><span><svg width="16" height="16" aria-hidden="true"><use href="#ok"/></svg>{{ $i['stackEnvio'][0] }}</span><em>{{ $i['stackEnvio'][1] }}</em></li>
                </ul>
                @if ($mercado['antesTexto'])<div class="valor"><span>{{ $i['valorSeparado'] }}</span><s class="num">{{ $mercado['antesTexto'] }} {{ $mercado['moneda'] }}</s></div>@endif
                <p class="mut renueva">{{ $i['renueva'] }}</p>
                <div class="checkout">
                    <a class="btn btn-pri btn-ancho" data-compra href="{{ $comprar() }}" {!! $ext !!}>@if ($compraExterna)<svg width="20" height="20" aria-hidden="true"><use href="#wa"/></svg>@endif{{ $i['pagar'][$modo] }}</a>
                    <div class="pasarela"><span>{{ $i['pagoSeguro'][$modo] }}</span>@foreach ($compraExterna ? $medios : $i['pasarela'] as $m)<span class="chip">{{ $m }}</span>@endforeach</div>
                </div>
                <div class="garantia"><span class="sello">{{ $i['sello'][0] }}<small>{{ $i['sello'][1] }}</small></span><span>{{ $i['garantia'] }}</span></div>
            </div>
            <div class="porque">
                <div class="head" style="margin:0">
                    <span class="eyebrow">{{ $i['precioEyebrow'] }}</span>
                    <h2>{{ $i['precioH'] }}</h2>
                    <p class="lead mut">{{ $i['precioP'] }}</p>
                </div>
                @foreach ($i['porque'] as [$ico, $h, $txt])
                    <div class="item"><span class="ic"><svg width="20" height="20" aria-hidden="true"><use href="#{{ $ico }}"/></svg></span><div><h3>{{ $h }}</h3><p>{{ $x($txt, [':moneda' => $mercado['moneda']]) }}</p></div></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 10. Preguntas --}}
    <section class="sec light" id="preguntas">
        <div class="wrap">
            <div class="head"><span class="eyebrow">{{ $i['faqEyebrow'] }}</span><h2>{{ $i['faqH'] }}</h2></div>
            <div class="faq">
                @foreach ($t['faqInicio'] as $n)
                    <details @if ($loop->first) open @endif><summary>{{ $t['faq'][$n][0] }}</summary><p>{{ $x($t['faq'][$n][1]) }}</p></details>
                @endforeach
            </div>
            <p style="margin-top:22px"><a class="btn btn-sec" href="{{ $ir($codigo, 'preguntas') }}">{{ $i['faqTodas'] }}</a></p>
        </div>
    </section>

    {{-- 11. Cierre --}}
    <section class="sec cierre">
        <div class="wrap">
            <span class="eyebrow">{{ $i['cierreEyebrow'] }}</span>
            <h2>{{ $i['cierreH'] }}</h2>
            <a class="btn btn-pri" data-compra href="{{ $comprar() }}" {!! $ext !!}>{{ $i['cta'] }} · {{ $mercado['precioTexto'] }} {{ $mercado['moneda'] }}</a>
            <span class="mut cierre-nota">{{ $envio }} · {{ $i['sinMensualidades'] }} · {{ $i['garantia30'] }}</span>
        </div>
    </section>
@endsection
