{{-- Lista de clientes del resumen: filtro por tarjeta, buscador y 20/50/100 por página --}}
@php
    $estados = [
        'activo' => ['Activo', 'up'], 'pronto' => ['Renueva pronto', ''], 'gracia' => ['En gracia', 'down'], 'pausa' => ['En pausa', 'down'],
    ];
    $f = fn ($fecha) => $fecha?->format('d/m/Y');
@endphp
<section class="dt-panel" id="clientes" style="margin-top:18px;scroll-margin-top:16px">
    <div class="dt-panel-h">
        <h2>Clientes · {{ \App\Services\ClientesAdmin::LISTAS[$lista] }} <span class="dt-chip">{{ number_format($clientes->total(), 0, ',', '.') }}</span></h2>
    </div>
    <form method="GET" action="/admin#clientes" class="ad-filtros">
        <nav class="dt-seg ad-tabs" aria-label="Filtrar clientes" style="margin:0">
            @foreach (\App\Services\ClientesAdmin::LISTAS as $id => $nombre)
                <a href="/admin?{{ http_build_query(['lista' => $id, 'buscar' => $buscar ?: null, 'por' => $por === 20 ? null : $por]) }}#clientes" class="{{ $lista === $id ? 'on' : '' }}">{{ $nombre }}</a>
            @endforeach
        </nav>
        <input type="hidden" name="lista" value="{{ $lista }}">
        <input class="dt-input" type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar por nombre, correo o usuario" aria-label="Buscar cliente" style="flex:1;min-width:200px;height:38px">
        <select class="dt-input" name="por" aria-label="Clientes por página" style="width:auto;height:38px" onchange="this.form.submit()">
            @foreach (\App\Services\ClientesAdmin::POR_PAGINA as $n)<option value="{{ $n }}" @selected($por === $n)>{{ $n }} por página</option>@endforeach
        </select>
        <button class="dt-btn" type="submit" style="height:38px">Buscar</button>
    </form>

    @if (! $filas)
        <p class="dt-sub" style="margin:14px 0 0">{{ $buscar !== '' ? 'Ningún cliente coincide con «'.$buscar.'».' : 'No hay clientes en esta lista.' }}</p>
    @else
        <div class="ad-scroll">
            <table class="ad-t">
                <thead><tr><th>Cliente</th><th>Perfil</th><th>Tarjeta</th><th>Activación</th><th>Compra</th><th>Uso</th><th></th></tr></thead>
                <tbody>
                @foreach ($filas as $c)
                    <tr>
                        <td>
                            <b>{{ $c['nombre'] }}</b>@if ($c['admin']) <span class="dt-chip" style="font-size:10px">Admin</span>@endif
                            <small>{{ $c['correo'] }}</small>
                            <small>{{ $c['celular'] ?: 'Sin celular' }} · desde {{ $f($c['registro']) }}</small>
                        </td>
                        <td>
                            @forelse ($c['perfiles'] as $p)
                                <a href="{{ $p['url'] }}" target="_blank" rel="noopener" style="display:block">/{{ $p['nickname'] }}</a>
                            @empty
                                <small>Sin perfil</small>
                            @endforelse
                        </td>
                        <td>
                            @if ($c['codigo'])
                                <code>{{ $c['codigo'] }}</code>
                                <span class="dt-chip {{ $estados[$c['estado']][1] }}" style="font-size:11px">{{ $estados[$c['estado']][0] }}</span>
                                <small>Vence {{ $f($c['venceEn']) }}</small>
                            @else
                                <span class="dt-chip" style="font-size:11px">Sin activar</span>
                                @if ($c['codigoCompra'])<small>Código de su compra: <code>{{ $c['codigoCompra'] }}</code></small>@endif
                            @endif
                        </td>
                        <td>
                            @if ($c['activadaEn'])
                                {{ $f($c['activadaEn']) }} <small>{{ $c['medio'] === 'codigo' ? 'Escribió el código' : ($c['medio'] === 'tarjeta' ? 'Acercó la tarjeta' : 'Antes de guardar el medio') }}</small>
                                <small>{{ $c['lugar'] ?: 'Lugar sin dato' }}</small>
                                <small>{{ $c['dispositivo'] ?: 'Dispositivo sin dato' }}</small>
                            @else
                                <small>—</small>
                            @endif
                        </td>
                        <td>
                            @if ($c['pedido'])
                                {{ $c['pedido']['codigo'] }} <small>{{ $c['pedido']['total'] }}</small><small>{{ $c['pedido']['acabado'] }} · {{ $f($c['pedido']['fecha']) }}</small>
                            @else
                                <small>Sin compra en línea</small>
                            @endif
                        </td>
                        <td>
                            {{ number_format($c['vistas'], 0, ',', '.') }} vistas
                            <small>{{ number_format($c['interacciones'], 0, ',', '.') }} en total</small>
                            <small>Último: {{ $c['ultimoUso']?->diffForHumans() ?? 'nunca' }}</small>
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            @if (! $c['codigo'] && $c['codigoCompra'])
                                <form method="POST" action="/admin/clientes/{{ $c['id'] }}/reenviar-codigo" style="display:inline">@csrf<button class="dt-btn" type="submit" title="Le vuelve a llegar el correo de su compra con el código">Reenviar código</button></form>
                            @endif
                            @php($digitos = preg_replace('/\D/', '', (string) $c['celular']))
                            @if ($digitos)<a class="dt-btn" href="https://wa.me/{{ strlen($digitos) === 10 ? '57'.$digitos : $digitos }}" target="_blank" rel="noopener">WhatsApp</a>@endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px">{{ $clientes->links() }}</div>
    @endif
</section>
