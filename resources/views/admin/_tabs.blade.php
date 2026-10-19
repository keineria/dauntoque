{{-- Pestañas del administrador --}}
@php($pestanas = ['admin' => 'Resumen', 'admin/carritos' => 'Carritos', 'admin/soporte' => 'Soporte', 'admin/correos' => 'Correos', 'admin/chips' => 'Chips'])
<nav class="dt-seg ad-tabs" aria-label="Administrador">
    @foreach ($pestanas as $ruta => $nombre)
        <a href="/{{ $ruta }}" class="{{ request()->is($ruta) || ($ruta !== 'admin' && request()->is($ruta . '/*')) ? 'on' : '' }}">{{ $nombre }}</a>
    @endforeach
</nav>
