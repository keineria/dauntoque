@props(['var' => 'p', 'solo' => false])
{{-- Miniatura del perfil público con su propio estilo. Necesita en Alpine un objeto con:
     tema, color, forma (clase de botones), estructura, foto, inicial. --}}
<div class="mini {{ $solo ? 'solo' : '' }}"
     :class="'t-' + ({{ $var }}.tema || 'crystal')"
     :style="`--acc:${ {{ $var }}.color || '#3b82f6' }; --fb:${ dtRadio({{ $var }}.forma) }`">
    <div class="card" :style="{{ $var }}.estructura === 'feria' ? 'width:40%' : ''">
        <template x-if="{{ $var }}.estructura === 'feria'">
            <div class="hero"><template x-if="{{ $var }}.foto"><img :src="{{ $var }}.foto" alt=""></template></div>
        </template>
        <template x-if="{{ $var }}.estructura !== 'feria'">
            <div class="av">
                <template x-if="{{ $var }}.foto"><img :src="{{ $var }}.foto" alt=""></template>
                <template x-if="!{{ $var }}.foto"><span x-text="{{ $var }}.inicial"></span></template>
            </div>
        </template>
        <div class="ln" style="width:70%"></div>
        <div class="ln s" style="width:50%"></div>
        <div class="row"><i></i><i></i><i></i><i></i></div>
        <div class="cta"></div>
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
