@extends('layouts.app')

@section('contenido')
    @php
        $fmt = fn ($n) => number_format($n, 0, ',', '.');
        $maxDia = max(1, $stats['ultimos14']->max());
        $fecha = fn ($d, $f) => \Illuminate\Support\Carbon::parse($d)->locale('es')->translatedFormat($f);
    @endphp

    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Analíticas</h1>
            <p class="dt-sub">Visitas y clics de todos tus perfiles públicos. No se cuentan tus propias visitas.</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:18px">
        <div class="dt-stat">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                <span>Vistas de perfil</span>
                @if(!is_null($stats['cambioSemanal']))
                    <span class="dt-chip {{ $stats['cambioSemanal'] >= 0 ? 'up' : 'down' }}">{{ $stats['cambioSemanal'] >= 0 ? '+' : '' }}{{ $stats['cambioSemanal'] }}% semana</span>
                @else
                    <span class="dt-chip">{{ $fmt($stats['semana']) }} esta semana</span>
                @endif
            </div>
            <b>{{ $fmt($stats['vistas']) }}</b>
            <span style="font-size:12px">{{ $fmt($stats['visitantes']) }} visitantes únicos</span>
        </div>
        <div class="dt-stat">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                <span>Contactos guardados</span>
                @if(!is_null($stats['tasaVcf']))<span class="dt-chip up">Tasa {{ $stats['tasaVcf'] }}%</span>@endif
            </div>
            <b>{{ $fmt($stats['vcf']) }}</b>
        </div>
        <div class="dt-stat">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                <span>Chats de WhatsApp</span>
                @if($stats['llamadas'])<span class="dt-chip">+{{ $fmt($stats['llamadas']) }} llamadas</span>@endif
            </div>
            <b>{{ $fmt($stats['whatsapp']) }}</b>
        </div>
        <div class="dt-stat">
            <span>Clics en Zelle / pagos</span>
            <b>{{ $fmt($stats['pagos']) }}</b>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:18px" class="dt-anal">
        <section class="dt-panel" style="margin:0">
            <div class="dt-panel-h"><h2>Visitas de los últimos 14 días</h2><span class="dt-sub" style="margin:0;font-size:12px">Una por persona y día</span></div>
            <div style="display:flex;align-items:flex-end;gap:6px;height:170px" role="img" aria-label="Visitas diarias de los últimos 14 días">
                @foreach($stats['ultimos14'] as $dia => $total)
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;gap:4px" title="{{ $fecha($dia, 'd M') }}: {{ $total }}">
                        <span style="font-size:10.5px;color:var(--muted);font-variant-numeric:tabular-nums">{{ $total ?: '' }}</span>
                        <div style="width:100%;border-radius:4px 4px 0 0;height:{{ max(3, round($total * 100 / $maxDia)) }}%;background:{{ $total ? 'var(--primary)' : 'var(--surface)' }}"></div>
                        <span style="font-size:10px;color:var(--muted);font-variant-numeric:tabular-nums">{{ $fecha($dia, 'd') }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="dt-panel" style="margin:0">
            <div class="dt-panel-h"><h2>Clics en redes sociales</h2></div>
            @forelse($stats['redes'] as $red => $total)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13.5px">
                    <span>{{ $red }}</span><b style="font-variant-numeric:tabular-nums">{{ $fmt($total) }}</b>
                </div>
            @empty
                <p class="dt-sub" style="margin:0">Todavía no hay clics en tus redes.</p>
            @endforelse
        </section>
    </div>
    <style>@media (max-width:860px){ .dt-anal { grid-template-columns:1fr !important; } }</style>

    <p class="dt-sub" style="margin-top:16px;font-size:12px">
        @if($stats['desde'])
            Datos registrados desde el {{ $fecha($stats['desde'], 'j \d\e F \d\e Y') }}.
        @else
            La medición está activa: las visitas y clics de tus perfiles públicos aparecerán aquí.
        @endif
    </p>
@endsection
