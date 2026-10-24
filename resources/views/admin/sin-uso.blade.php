@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Sin usar su tarjeta o enlace</h1>
            <p class="dt-sub">Clientes con {{ $meses }} meses o más sin visitas ni clics en su perfil. Mándales un recordatorio con ideas para usarla.</p>
        </div>
        <a href="/admin" class="dt-btn">Volver al resumen</a>
    </div>
    @include('admin._tabs')

    @unless ($activa)
        <p class="dt-panel" style="color:var(--warn-t);margin:0 0 18px">El recordatorio de uso está apagado. Enciéndelo en <a href="/admin/correos" style="color:inherit">Correos</a> para poder enviarlo.</p>
    @endunless

    <section class="dt-panel">
        <div class="dt-panel-h">
            <h2>Recordatorio de uso</h2>
            <a class="dt-btn" href="/admin/correos/plantilla/recordatorio_uso">Editar el correo</a>
        </div>
        <p class="dt-sub" style="margin:0 0 14px">A cada persona se le manda como máximo uno cada {{ $dias }} días. {{ $pendientes }} {{ $pendientes === 1 ? 'persona puede' : 'personas pueden' }} recibirlo ahora.</p>
        @if ($activa && $pendientes > 0)
            <form method="POST" action="/admin/sin-uso/enviar-todos" onsubmit="return confirm('¿Enviar el recordatorio a {{ $pendientes }} {{ $pendientes === 1 ? 'persona' : 'personas' }}?')">
                @csrf<button class="dt-btn pri" type="submit">Enviar a todos los que faltan ({{ $pendientes }})</button>
            </form>
        @endif
    </section>

    <section class="dt-panel">
        <div class="dt-panel-h"><h2>Clientes <span class="dt-chip">{{ $clientes->total() }}</span></h2></div>
        <form method="GET" action="/admin/sin-uso" class="ad-filtros">
            <input class="dt-input" type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar por nombre, correo o usuario" aria-label="Buscar cliente" style="flex:1;min-width:200px;height:38px">
            <select class="dt-input" name="por" aria-label="Clientes por página" style="width:auto;height:38px" onchange="this.form.submit()">
                @foreach (\App\Services\ClientesAdmin::POR_PAGINA as $n)<option value="{{ $n }}" @selected($por === $n)>{{ $n }} por página</option>@endforeach
            </select>
            <button class="dt-btn" type="submit" style="height:38px">Buscar</button>
        </form>
        @if (! $filas)
            <p class="dt-sub" style="margin:14px 0 0">{{ $buscar !== '' ? 'Nadie coincide con «'.$buscar.'».' : 'Todos tus clientes han tenido visitas en los últimos '.$meses.' meses.' }}</p>
        @else
            <div class="ad-scroll">
                <table class="ad-t">
                    <thead><tr><th>Cliente</th><th>Perfil</th><th>Último uso</th><th>Tarjeta</th><th>Recordatorio</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($filas as $c)
                        @php($puede = ! $c['recordatorioEn'] || $c['recordatorioEn']->lt(now()->subDays($dias)))
                        <tr>
                            <td><a class="ad-nombre" href="/admin/clientes/{{ $c['id'] }}?desde={{ urlencode('/admin/sin-uso') }}">{{ $c['nombre'] }}</a><small>{{ $c['correo'] }}</small><small>{{ $c['celular'] ?: 'Sin celular' }} · desde {{ $c['registro']->format('d/m/Y') }}</small></td>
                            <td>@foreach ($c['perfiles'] as $p)<a href="{{ $p['url'] }}" target="_blank" rel="noopener" style="display:block">/{{ $p['nickname'] }}</a>@endforeach</td>
                            <td>{{ $c['ultimoUso']?->format('d/m/Y') ?? 'Nunca' }}<small>{{ $c['vistas'] }} vistas en total</small></td>
                            <td>@if ($c['codigo'])<code>{{ $c['codigo'] }}</code><small>Activada {{ $c['activadaEn']?->format('d/m/Y') }}</small>@else<small>Sin activar</small>@endif</td>
                            <td>{{ $c['recordatorioEn']?->format('d/m/Y') ?? 'Nunca' }}</td>
                            <td style="text-align:right;white-space:nowrap">
                                @if ($activa && $puede)
                                    <form method="POST" action="/admin/sin-uso/{{ $c['id'] }}/enviar" style="display:inline">@csrf<button class="dt-btn pri" type="submit">Enviar recordatorio</button></form>
                                @elseif (! $puede)
                                    <span class="dt-chip up">Enviado</span>
                                @endif
                                @php($digitos = preg_replace('/\D/', '', (string) $c['celular']))
                                @if ($digitos)<a class="dt-btn" href="https://wa.me/{{ strlen($digitos) === 10 ? '57'.$digitos : $digitos }}?text={{ rawurlencode('Hola '.\Illuminate\Support\Str::before($c['nombre'], ' ').', te escribimos de DaUnToque. ¿Cómo te ha ido con tu tarjeta?') }}" target="_blank" rel="noopener">WhatsApp</a>@endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:12px">{{ $clientes->links() }}</div>
        @endif
    </section>
@endsection
