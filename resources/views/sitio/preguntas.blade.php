@extends('sitio.layout')
@php
    $p = $t['preguntas'];
    $faq = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($q) => [
        '@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $x($q[1])],
    ], $t['faq'])];
@endphp

@section('titulo', $p['titulo'])
@section('descripcion', $x($p['descripcion']))
@section('schema')
    <script type="application/ld+json">{!! json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endsection

@section('contenido')
    <section class="sec light pagina-faq">
        <div class="wrap">
            <div class="head">
                <span class="eyebrow">{{ $t['inicio']['faqEyebrow'] }}</span>
                <h1>{{ $p['h1'] }}</h1>
                <p class="lead mut">{{ $p['sub'] }}</p>
            </div>
            <div class="faq">
                @foreach ($t['faq'] as $q)
                    <details @if ($loop->first) open @endif><summary>{{ $q[0] }}</summary><p>{{ $x($q[1]) }}</p></details>
                @endforeach
            </div>
            <div class="ctas" style="margin-top:28px" data-compra>
                <a class="btn btn-wa" href="{{ $wa($t['waDuda']) }}" target="_blank" rel="noopener"><svg width="20" height="20" aria-hidden="true"><use href="#wa"/></svg>{{ $t['escribenos'] }}</a>
                <a class="btn btn-pri" href="{{ $comprar() }}" @if($compraExterna) target="_blank" rel="noopener" @endif>{{ $t['nav']['comprar'] }} · {{ $mercado['precioTexto'] }} {{ $mercado['moneda'] }}</a>
            </div>
        </div>
    </section>
@endsection
