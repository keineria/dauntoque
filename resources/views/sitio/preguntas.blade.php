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
    <section class="seccion">
        <div class="envoltura">
            <div class="cabeza centro">
                <h1 style="font-size:clamp(32px,7vw,48px);font-weight:850">{{ $p['h1'] }}</h1>
                <p>{{ $p['sub'] }}</p>
            </div>
            <div class="faq">
                @foreach ($t['faq'] as $q)
                    <details @if ($loop->first) open @endif><summary>{{ $q[0] }}</summary><p>{{ $x($q[1]) }}</p></details>
                @endforeach
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-top:28px" data-compra>
                <a class="btn btn-wa" href="{{ $wa($t['waDuda']) }}" target="_blank" rel="noopener">@include('sitio._ico', ['n' => 'wa']) {{ $t['escribenos'] }}</a>
                <a class="btn btn-pri" href="{{ $wa($t['wa']) }}" target="_blank" rel="noopener">{{ $t['nav']['comprar'] }} · {{ $mercado['precioTexto'] }} {{ $mercado['moneda'] }}</a>
            </div>
        </div>
    </section>
@endsection
