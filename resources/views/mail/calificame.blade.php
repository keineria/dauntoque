@php
    $paraDuenio = $tipo === 'baja';
    $c = $calificacion;
    $nombreCliente = $cita ? \Illuminate\Support\Str::before($cita->cliente_nombre, ' ') : null;
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $asunto }}</title></head>
<body style="margin:0;background:#f5f4ef;font-family:Helvetica,Arial,sans-serif;color:#151816">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f4ef;padding:24px 12px">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden">
    <tr><td style="background:#0d0f10;padding:26px 28px;color:#eef1ee">
        <div style="font-size:20px;font-weight:800">dauntoque<span style="color:#2fae6e">.com</span></div>
        <div style="margin-top:18px;font-size:24px;font-weight:800;line-height:1.2">
            @if($paraDuenio)Alguien no quedó del todo contento.@else{{ app()->getLocale() === 'en' ? 'Hi' : 'Hola' }}@if($nombreCliente), {{ $nombreCliente }}@endif. {{ $asunto }}@endif
        </div>
    </td></tr>
    <tr><td style="padding:26px 28px">
        @if($paraDuenio)
            <p style="margin:0 0 18px;font-size:16px;line-height:1.5">Esta calificación no sale en tu perfil. Respóndele pronto: un cliente bien atendido después de una queja suele volver.</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f4ef;border-radius:14px">
                <tr><td style="padding:16px 18px;font-size:15px;line-height:1.6">
                    <span style="font-size:20px;color:#d97706;letter-spacing:2px">{{ str_repeat('★', $c->estrellas) }}</span><span style="font-size:20px;color:#c9c6bb;letter-spacing:2px">{{ str_repeat('★', 5 - $c->estrellas) }}</span><br>
                    @if($c->comentario)<i>“{{ $c->comentario }}”</i><br>@endif
                    {{ $c->nombre ?: 'Sin nombre' }}@if($c->contacto) · {{ $c->contacto }}@endif
                    @if($c->cita)<br>Cita: {{ $c->cita->servicio_nombre }}@endif
                </td></tr>
            </table>
        @else
            <p style="margin:0 0 18px;font-size:16px;line-height:1.5">{{ __('calificame.texto_pedir', ['nombre' => $negocio]) }}</p>
            <div style="font-size:34px;color:#f59e0b;letter-spacing:6px;text-align:center">★★★★★</div>
        @endif

        @php $boton = $paraDuenio ? ['Ver mis calificaciones', url('/dashboard/calificame')] : ($enlace ? [__('calificame.boton_pedir'), $enlace] : null); @endphp
        @if($boton)
            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px 0 0"><tr><td style="background:#2fae6e;border-radius:999px">
                <a href="{{ $boton[1] }}" style="display:inline-block;padding:14px 26px;color:#04140b;font-weight:700;font-size:15px;text-decoration:none">{{ $boton[0] }}</a>
            </td></tr></table>
        @endif
    </td></tr>
    <tr><td style="padding:18px 28px;background:#ebe9e1;font-size:12px;color:#5d655f">{{ $paraDuenio ? 'Califícame · DaUnToque' : __('calificame.pie') }}</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
