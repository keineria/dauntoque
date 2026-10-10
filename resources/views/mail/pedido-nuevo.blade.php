<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Pedido {{ $pedido->codigo }}</title></head>
<body style="font-family:Helvetica,Arial,sans-serif;color:#151816">
    <h2 style="margin:0 0 12px">Pedido pagado {{ $pedido->codigo }}</h2>
    <table cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse">
        <tr><td><b>Cliente</b></td><td>{{ $pedido->nombre }}</td></tr>
        <tr><td><b>Correo</b></td><td>{{ $pedido->correo }}</td></tr>
        <tr><td><b>Celular</b></td><td>{{ $pedido->celular }}</td></tr>
        @if($pedido->documento)<tr><td><b>Documento</b></td><td>{{ $pedido->documento }}</td></tr>@endif
        <tr><td><b>Dirección</b></td><td>{{ $pedido->direccion }}, {{ $pedido->ciudad }} ({{ $pedido->departamento }})</td></tr>
        <tr><td><b>Tarjeta</b></td><td>{{ $pedido->acabadoNombre() }}@if($pedido->nombre_tarjeta) · nombre: {{ $pedido->nombre_tarjeta }}@endif</td></tr>
        <tr><td><b>Total</b></td><td>{{ $pedido->totalTexto() }} · Bold {{ $pedido->pago_proveedor_id }}</td></tr>
    </table>
</body></html>
