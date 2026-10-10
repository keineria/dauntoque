{!! '<' . '?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($paginas as $p)
    <url>
        <loc>{{ $dominio }}/{{ $p['codigo'] }}/{{ $p['ruta'] === '' ? '' : $p['ruta'] . '/' }}</loc>
@foreach (\App\Console\Commands\GenerarSitio::VERSIONES as $c => $v)
@php $r = \App\Console\Commands\GenerarSitio::rutaDe($c, $p['vista'], $p['acabado']); @endphp
        <xhtml:link rel="alternate" hreflang="{{ $v['hreflang'] }}" href="{{ $dominio }}/{{ $c }}/{{ $r === '' ? '' : $r . '/' }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $dominio }}/"/>
    </url>
@endforeach
</urlset>
