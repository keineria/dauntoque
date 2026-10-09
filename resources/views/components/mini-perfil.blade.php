@props(['var' => 'p', 'solo' => false])
{{-- Miniatura del perfil público con su propio estilo y diseño. Necesita en Alpine un objeto con:
     tema, color, forma (clase de botones), estructura, foto, inicial. --}}
<div class="mini {{ $solo ? 'solo' : '' }}"
     :class="'t-' + ({{ $var }}.tema || 'crystal')"
     :style="`--acc:${ {{ $var }}.color || '#3b82f6' }; --fb:${ dtRadio({{ $var }}.forma) }`">
    <div class="card" :style="{{ $var }}.estructura === 'feria' ? 'width:40%' : ''">
        {{-- Encabezado según el diseño --}}
        <template x-if="{{ $var }}.estructura === 'feria'">
            <div class="hero"><template x-if="{{ $var }}.foto"><img :src="{{ $var }}.foto" alt=""></template></div>
        </template>
        <template x-if="{{ $var }}.estructura === 'ejecutiva'">
            <div class="biz">
                <div class="av"><template x-if="{{ $var }}.foto"><img :src="{{ $var }}.foto" alt=""></template></div>
                <div style="flex:1;display:flex;flex-direction:column;gap:3px"><div class="ln" style="width:90%"></div><div class="ln s" style="width:60%"></div></div>
            </div>
        </template>
        <template x-if="!['feria', 'ejecutiva'].includes({{ $var }}.estructura)">
            <div class="av">
                <template x-if="{{ $var }}.foto"><img :src="{{ $var }}.foto" alt=""></template>
                <template x-if="!{{ $var }}.foto"><span x-text="{{ $var }}.inicial"></span></template>
            </div>
        </template>
        <template x-if="{{ $var }}.estructura !== 'ejecutiva'">
            <div style="width:100%;display:flex;flex-direction:column;align-items:center;gap:4px"><div class="ln" style="width:70%"></div><div class="ln s" style="width:50%"></div></div>
        </template>

        {{-- Cuerpo: botones en columna (Enlaces, Ejecutivo) o en cuadrícula (Clásico, Moderno, Feria) --}}
        <template x-if="['enlaces', 'ejecutiva'].includes({{ $var }}.estructura)">
            <div class="stack"><div class="cta"></div><i></i><i></i><i></i></div>
        </template>
        <template x-if="!['enlaces', 'ejecutiva'].includes({{ $var }}.estructura)">
            <div style="width:100%;margin-top:auto;display:flex;flex-direction:column;gap:5px"><div class="row" style="margin:0"><i></i><i></i><i></i><i></i></div><div class="cta"></div></div>
        </template>
    </div>
    <div class="sp"><i></i><i></i><i></i></div>
</div>
@once
    <script>
        // Radio de los botones de la miniatura según la forma elegida en el perfil
        function dtRadio(forma) {
            return ({ 'rounded-none': '1px', '-skew-x-12': '1px', 'rounded-md': '3px', 'rounded-xl': '5px', 'rounded-2xl': '6px', 'rounded-full': '999px' })[forma] || '6px';
        }
    </script>
@endonce
