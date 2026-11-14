{{-- Idioma sin país (es, en) y x-default: a quién mostrarle cada versión fuera de Colombia y Estados Unidos --}}
@foreach (\App\Console\Commands\GenerarSitio::GENERICOS as $idiomaGenerico => $c)
<link rel="alternate" hreflang="{{ $idiomaGenerico }}" href="{{ $canonica($c) }}">
@endforeach
