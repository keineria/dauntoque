@php
    $c = $cita;
    $nombreCliente = $c ? \Illuminate\Support\Str::before($c->cliente_nombre, ' ') : null;
    if ($tipo === 'codigo') {
        $titulo = __('agenda.mis_citas');
        $texto = __('agenda.texto_codigo', ['minutos' => config('agenda.codigo_minutos')]);
    } elseif ($paraDuenio) {
        $titulo = match ($tipo) {
            'nueva' => $c->estado === 'pendiente' ? 'Tienes una cita por aprobar.' : 'Te agendaron una cita.',
            'cambio_pedido' => $c->cliente_nombre . ' pide otra hora.',
            'cambio_aceptado' => $c->cliente_nombre . ' aceptó la nueva hora.',
            'cambio_rechazado' => $c->cliente_nombre . ' prefiere su hora original.',
            default => $c->cliente_nombre . ' canceló su cita.',
        };
        $texto = match ($tipo) {
            'nueva' => $c->estado === 'pendiente' ? 'Apruébala o recházala desde tu agenda. El cliente recibe la respuesta por correo.' : 'Ya quedó confirmada y el cliente recibió su correo.',
            'cambio_pedido' => 'Pide pasarla al ' . $c->fechaLocal($zona, $c->propuesta_inicia_en) . ' a las ' . $c->horaLocal($zona, $c->propuesta_inicia_en) . '. Mientras respondes, sigue a la hora de abajo.',
            'cambio_aceptado' => 'La cita quedó en la hora de abajo.',
            'cambio_rechazado' => 'La cita sigue a la hora de abajo.',
            default => 'Esa hora quedó libre otra vez en tu agenda.',
        };
    } else {
        $titulo = $asunto;
        $texto = __('agenda.texto_' . $tipo, [
            'nombre' => $negocio,
            'fecha' => $c->propuesta_inicia_en ? $c->fechaLocal($zona, $c->propuesta_inicia_en) : '',
            'hora' => $c->propuesta_inicia_en ? $c->horaLocal($zona, $c->propuesta_inicia_en) : '',
        ]);
    }
    $boton = $paraDuenio
        ? ['Abrir mi agenda', url('/dashboard/agenda?fecha=' . $c->inicia_en->copy()->setTimezone($zona)->format('Y-m-d'))]
        : ($tipo === 'codigo' ? null : (in_array($tipo, ['rechazada', 'cancelada'], true) && $perfil
            ? [__('agenda.boton_reservar'), \App\Models\Profile::urlPublica(\App\Models\Profile::maestroDe($c->user_id)?->nickname ?? '') . '/agenda']
            : [__('agenda.boton_ver'), $c->enlace()]));
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $titulo }}</title></head>
<body style="margin:0;background:#f5f4ef;font-family:Helvetica,Arial,sans-serif;color:#151816">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f4ef;padding:24px 12px">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden">
    <tr><td style="background:#0d0f10;padding:26px 28px;color:#eef1ee">
        <div style="font-size:20px;font-weight:800">dauntoque<span style="color:#2fae6e">.com</span></div>
        <div style="margin-top:18px;font-size:24px;font-weight:800;line-height:1.2">@if(!$paraDuenio && $nombreCliente && $tipo !== 'codigo'){{ app()->getLocale() === 'en' ? 'Hi' : 'Hola' }}, {{ $nombreCliente }}. @endif{{ $titulo }}</div>
    </td></tr>
    <tr><td style="padding:26px 28px">
        <p style="margin:0 0 18px;font-size:16px;line-height:1.5">{{ $texto }}</p>

        @if($tipo === 'codigo')
            <div style="font-size:36px;font-weight:800;letter-spacing:.3em;text-align:center;background:#f5f4ef;border-radius:14px;padding:18px">{{ $extra['codigo'] }}</div>
        @else
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f4ef;border-radius:14px">
                <tr><td style="padding:16px 18px;font-size:15px;line-height:1.6">
                    <b style="font-size:17px">{{ $c->servicio_nombre }}</b>@if(!$paraDuenio) · {{ $negocio }}@endif<br>
                    <span style="{{ $c->estado === 'cancelada' ? 'text-decoration:line-through;color:#8a918c' : '' }}">{{ \Illuminate\Support\Str::ucfirst($c->fechaLocal($zona)) }} · {{ $c->horaLocal($zona) }} – {{ $c->horaLocal($zona, $c->termina_en) }}</span><br>
                    @if($paraDuenio)
                        {{ $c->cliente_nombre }} · {{ $c->cliente_correo }}@if($c->cliente_whatsapp) · {{ $c->cliente_whatsapp }}@endif
                        @if($c->nota)<br><i>“{{ $c->nota }}”</i>@endif
                    @elseif($perfil?->direccion)
                        {{ $perfil->direccion }}
                    @endif
                    @if($c->motivo && $c->estado === 'cancelada')<br>{{ __('agenda.motivo', ['motivo' => $c->motivo]) }}@endif
                </td></tr>
            </table>
        @endif

        @if($boton)
            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px 0 0"><tr><td style="background:#2fae6e;border-radius:999px">
                <a href="{{ $boton[1] }}" style="display:inline-block;padding:14px 26px;color:#04140b;font-weight:700;font-size:15px;text-decoration:none">{{ $boton[0] }}</a>
            </td></tr></table>
        @endif
    </td></tr>
    <tr><td style="padding:18px 28px;background:#ebe9e1;font-size:12px;color:#5d655f">{{ $paraDuenio ? 'Agenda de citas · DaUnToque' : __('agenda.pie') }}</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
