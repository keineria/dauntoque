@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Apps</h1>
            <p class="dt-sub">Activa solo lo que necesitas. Las apps activas aparecen en el menú, en "Apps activas".</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
        @foreach($apps as $a)
            @php $activa = $a['app'] ?? null; @endphp
            <section class="dt-panel" style="display:flex;flex-direction:column;gap:12px;margin:0;{{ !empty($a['proximamente']) ? 'opacity:.6' : '' }}">
                <div style="display:flex;gap:12px;align-items:center">
                    <span style="width:46px;height:46px;border-radius:14px;background:var(--soft-2);display:grid;place-items:center;font-size:24px;flex:none">{{ $a['icono'] }}</span>
                    <div>
                        <b style="font-size:15px">{{ $a['nombre'] }}</b><br>
                        @if(!empty($a['proximamente']))
                            <span class="dt-chip">Próximamente</span>
                        @elseif($activa && $activa->vigente())
                            <span class="dt-chip up">Activa</span>
                        @elseif($activa)
                            <span class="dt-chip down">Vencida</span>
                        @endif
                    </div>
                </div>
                <p class="dt-sub" style="margin:0;flex:1">{{ $a['resumen'] }}</p>
                @if(empty($a['proximamente']))
                    @if($activa)
                        <span class="dt-sub" style="margin:0;font-size:12px">{{ $activa->vigente() ? 'Activa hasta el ' . $activa->vence_en->format('d/m/Y') . '.' : 'Venció el ' . $activa->vence_en->format('d/m/Y') . '.' }}</span>
                        <a class="dt-btn pri" href="{{ $a['abrir'] }}" style="justify-content:center">Abrir</a>
                    @else
                        <span class="dt-sub" style="margin:0;font-size:12px">{{ $a['precio'] }}</span>
                        <div style="display:flex;gap:8px">
                            <form method="POST" action="{{ $a['activar'] }}" style="flex:1">@csrf<button class="dt-btn pri" type="submit" style="width:100%;justify-content:center">Activar</button></form>
                            <a class="dt-btn" href="{{ $a['abrir'] }}">Ver más</a>
                        </div>
                    @endif
                @endif
            </section>
        @endforeach
    </div>
@endsection
