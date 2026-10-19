@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">{{ $def['nombre'] }}</h1>
            <p class="dt-sub">{{ $def['cuando'] }} @unless($activa)<b style="color:#fbbf24">Esta plantilla está apagada.</b>@endunless</p>
        </div>
        <a href="/admin/correos" class="dt-btn">Volver a Correos</a>
    </div>
    @include('admin._tabs')

    @if ($errors->any())<p class="dt-panel" style="color:#f87171;margin:0 0 18px">{{ $errors->first() }}</p>@endif

    <form method="POST" action="/admin/correos/plantilla/{{ $id }}" class="pl-editor"
          x-data="plantillaEditor(@js(['asunto' => $asunto, 'html' => $html, 'ejemplo' => $ejemplo]))">
        @csrf
        <section class="dt-panel pl-col">
            <div>
                <label class="dt-label" for="pl-asunto">Asunto</label>
                <input id="pl-asunto" class="dt-input" name="asunto" x-model="asunto" maxlength="200" required x-ref="asunto" @focus="campo = $refs.asunto">
            </div>
            <div>
                <div class="dt-label">Campos que se cambian solos <span style="font-weight:400">(clic para insertarlo donde está el cursor)</span></div>
                <div class="pl-campos">
                    @foreach ($def['campos'] as $clave => $desc)
                        <button type="button" class="pl-campo" @click="insertar('{{ $clave }}')" title="{{ $desc }} · ejemplo: {{ $ejemplo[$clave] ?? '' }}">
                            <code>&#123;&#123;{{ $clave }}&#125;&#125;</code><span>{{ $desc }}</span>
                        </button>
                    @endforeach
                </div>
                <p class="dt-sub" style="font-size:12px;margin:8px 0 0">Si un dato puede venir vacío, pon un texto de respaldo: <code>&#123;&#123;nombre|amigo&#125;&#125;</code>.</p>
            </div>
            <div style="display:flex;flex-direction:column;flex:1">
                <label class="dt-label" for="pl-html">Contenido en HTML</label>
                <textarea id="pl-html" name="html" class="dt-input pl-codigo" x-model="html" x-ref="html" @focus="campo = $refs.html" spellcheck="false" required></textarea>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px">
                <button class="dt-btn pri" type="submit" name="accion" value="guardar">Guardar plantilla</button>
                <button class="dt-btn" type="submit" name="accion" value="probar">Enviarme una prueba</button>
                @if ($propia)
                    <button class="dt-btn" type="submit" form="pl-restaurar">Restaurar el original</button>
                @endif
            </div>
        </section>

        <section class="dt-panel pl-col">
            <div class="dt-panel-h" style="margin:0">
                <h2>Vista previa</h2>
                <div class="dt-seg" role="group" aria-label="Tamaño de la vista previa">
                    <button type="button" :class="movil ? '' : 'on'" @click="movil = false">Computador</button>
                    <button type="button" :class="movil ? 'on' : ''" @click="movil = true">Celular</button>
                </div>
            </div>
            <div class="pl-asunto"><b x-text="llenar(asunto, false)"></b><small>De: tu remitente · Para: {{ $ejemplo['correo'] ?? 'cliente' }}</small></div>
            <div class="pl-marco"><iframe title="Vista previa del correo" sandbox="" :srcdoc="llenar(html, true)" :style="movil ? 'width:375px' : 'width:100%'"></iframe></div>
            <p class="dt-sub" style="font-size:12px;margin:0">Con datos de ejemplo (María Pérez, Medellín). Cada persona verá los suyos.</p>
        </section>
    </form>
    @if ($propia)
        <form id="pl-restaurar" method="POST" action="/admin/correos/plantilla/{{ $id }}/restaurar" onsubmit="return confirm('¿Volver al diseño original? Se pierde el que guardaste.')">@csrf</form>
    @endif

    <style>
        .pl-editor { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:18px; align-items:stretch; }
        .pl-col { display:flex; flex-direction:column; gap:14px; margin:0; }
        .pl-campos { display:flex; flex-wrap:wrap; gap:6px; max-height:150px; overflow-y:auto; }
        .pl-campo { display:flex; flex-direction:column; align-items:flex-start; gap:1px; padding:6px 10px; border:1px solid var(--border); border-radius:10px; background:rgba(0,0,0,.25); color:var(--text); cursor:pointer; text-align:left; }
        .pl-campo:hover { border-color:#3b82f6; }
        .pl-campo code { font-family:ui-monospace,Consolas,monospace; font-size:12px; color:#60a5fa; }
        .pl-campo span { font-size:11px; color:var(--muted); }
        .pl-codigo { flex:1; min-height:420px; font-family:ui-monospace,Consolas,monospace; font-size:12.5px; line-height:1.5; white-space:pre; tab-size:2; }
        .pl-asunto { padding:10px 12px; border:1px solid var(--border); border-radius:12px; font-size:14px; }
        .pl-asunto small { display:block; color:var(--muted); font-size:12px; margin-top:2px; }
        .pl-marco { flex:1; min-height:520px; background:#f5f4ef; border-radius:12px; overflow:auto; display:flex; justify-content:center; }
        .pl-marco iframe { border:0; min-height:520px; height:100%; background:#fff; transition:width .2s; }
        @media (max-width: 1000px) { .pl-editor { grid-template-columns:1fr; } }
    </style>
    <script>
        function plantillaEditor(datos) {
            const escapar = (t) => String(t).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
            return {
                ...datos, movil: false, campo: null,
                // Igual que Plantillas::llenar en el servidor
                llenar(texto, html) {
                    return String(texto || '').replace(/\{\{\s*([a-z_]+)\s*(?:\|([^}]*))?\}\}/g, (_, clave, respaldo) => {
                        const valor = String(this.ejemplo[clave] ?? '') || (respaldo || '').trim();
                        return html ? escapar(valor) : valor;
                    });
                },
                insertar(clave) {
                    const el = this.campo || this.$refs.html, prop = el === this.$refs.asunto ? 'asunto' : 'html';
                    const ini = el.selectionStart ?? this[prop].length, fin = el.selectionEnd ?? ini, token = '{{' + clave + '}}';
                    this[prop] = this[prop].slice(0, ini) + token + this[prop].slice(fin);
                    this.$nextTick(() => { el.focus(); el.setSelectionRange(ini + token.length, ini + token.length); });
                },
            };
        }
    </script>
@endsection
