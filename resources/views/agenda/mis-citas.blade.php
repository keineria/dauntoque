@extends('agenda._base')

@section('titulo', __('agenda.mis_citas'))

@section('contenido')
    <h1>{{ __('agenda.mis_citas') }}</h1>

    @if(session('aviso'))
        <div class="ag-aviso" role="status" style="margin-top:14px">{{ session('aviso') }}</div>
    @endif
    @if($errors->any())
        <div class="ag-aviso err" role="alert" style="margin-top:14px">{{ $errors->first() }}</div>
    @endif

    @if(!$correo)
        <p class="ag-muted">{{ __('agenda.mis_citas_ayuda') }}</p>
        <form method="POST" action="/mis-citas/codigo">
            @csrf
            <label for="correo">{{ __('agenda.correo') }}</label>
            <input class="ag-in" id="correo" type="email" name="correo" value="{{ old('correo', $pendiente) }}" required autocomplete="email">
            <button class="ag-btn {{ $pendiente ? 'sec' : '' }}" type="submit">{{ __('agenda.enviar_codigo') }}</button>
        </form>
        @if($pendiente)
            <form method="POST" action="/mis-citas/entrar" style="margin-top:8px">
                @csrf
                <label for="codigo">{{ __('agenda.codigo') }}</label>
                <input class="ag-in" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required style="letter-spacing:.3em;font-size:20px;text-align:center">
                <button class="ag-btn" type="submit">{{ __('agenda.entrar') }}</button>
            </form>
        @endif
    @else
        <div class="ag-muted">{{ $correo }} · <form method="POST" action="/mis-citas/salir" style="display:inline">@csrf<button class="ag-link" type="submit">{{ __('agenda.salir') }}</button></form></div>

        @foreach([__('agenda.proximas') => $proximas, __('agenda.pasadas') => $pasadas] as $titulo => $lista)
            <h2>{{ $titulo }}</h2>
            @forelse($lista as $c)
                @php $zona = $zonas[$c->user_id] ?? 'America/Bogota'; $estado = $c->estadoVisible(); @endphp
                <a class="ag-op" href="/cita/{{ $c->token }}" style="text-decoration:none">
                    <span>
                        <b>{{ $c->servicio_nombre }}</b>
                        <small>{{ $negocios[$c->user_id]->nombre ?? '' }}</small>
                        <small>{{ \Illuminate\Support\Str::ucfirst($c->fechaLocal($zona)) }} · {{ $c->horaLocal($zona) }}</small>
                    </span>
                    <span class="ag-chip {{ $estado }}">{{ __('agenda.estado_' . $estado) }}</span>
                </a>
            @empty
                <p class="ag-muted">{{ __('agenda.sin_citas') }}</p>
            @endforelse
        @endforeach
    @endif
@endsection
