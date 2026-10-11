{{-- Formulario de un servicio de la agenda (nuevo si $s es null) --}}
<form method="POST" action="/dashboard/agenda/servicios" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;align-items:end;margin-top:12px">
    @csrf
    @if($s)<input type="hidden" name="id" value="{{ $s->id }}">@endif
    <div style="grid-column:span 2"><span class="dt-label">Nombre</span><input class="dt-input" name="nombre" required maxlength="80" value="{{ $s->nombre ?? '' }}" placeholder="Corte de cabello"></div>
    <div><span class="dt-label">Duración (min)</span><input class="dt-input" type="number" name="duracion_min" required min="5" max="720" step="5" value="{{ $s->duracion_min ?? 30 }}"></div>
    <div><span class="dt-label">Descanso después (min)</span><input class="dt-input" type="number" name="colchon_min" min="0" max="240" step="5" value="{{ $s->colchon_min ?? 0 }}"></div>
    <div><span class="dt-label">Precio {{ $moneda }} (opcional)</span><input class="dt-input" type="number" name="precio" min="0" step="any" value="{{ isset($s->precio) ? (float) $s->precio : '' }}"></div>
    <div style="grid-column:1/-1"><span class="dt-label">Descripción (opcional)</span><input class="dt-input" name="descripcion" maxlength="200" value="{{ $s->descripcion ?? '' }}"></div>
    <label style="grid-column:1/-1;display:flex;gap:8px;align-items:center;cursor:pointer">
        <input type="checkbox" name="requiere_aprobacion" value="1" @checked($s->requiere_aprobacion ?? false)>
        Pedir mi aprobación antes de confirmar las reservas de este servicio
    </label>
    <div><button class="dt-btn pri" type="submit">{{ $s ? 'Guardar cambios' : 'Crear servicio' }}</button></div>
</form>
