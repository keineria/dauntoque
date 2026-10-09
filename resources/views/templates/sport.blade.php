<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $perfil->nombre }} | DaUnToque</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@500;700&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #000000; color: #ffffff; background-image: radial-gradient(#1f2937 1px, transparent 1px); background-size: 20px 20px; }
        h1 { font-family: 'Teko', sans-serif; text-transform: uppercase; letter-spacing: 1px; }
        .skew-btn { transform: skewX(-12deg); transition: all 0.2s; }
        .skew-content { transform: skewX(12deg); display: inline-block; }
    </style>
</head>
<body class="flex justify-center min-h-screen" x-data="multidioma()">
    <main class="w-full max-w-md px-4 py-8 flex flex-col relative z-10">
        
        <div class="bg-[#111] border-l-4 border-[#ccff00] rounded-xl p-6 mb-6 flex-1 flex flex-col shadow-2xl relative overflow-hidden">
            <!-- Adorno gráfico -->
            <div class="absolute -right-10 -top-10 text-[#ccff00] opacity-10 font-black text-9xl italic">///</div>

            <div class="flex flex-col items-center text-center mb-6 relative z-10">
                <div class="w-32 h-32 mb-4 border-2 border-[#ccff00] p-1 shadow-[0_0_20px_rgba(204,255,0,0.3)] {{ ($perfil->tipo_perfil == 'empresa') ? 'rounded-none' : 'rounded-full' }}">
                    @if($perfil->foto_perfil)
                        <img src="{{ $perfil->foto_perfil }}" class="w-full h-full object-cover {{ ($perfil->tipo_perfil == 'empresa') ? '' : 'rounded-full' }} grayscale hover:grayscale-0 transition-all">
                    @endif
                </div>
                <h1 class="text-4xl text-white leading-none">{{ $perfil->nombre }}</h1>
                @if($perfil->tipo_perfil !== 'empresa')
                    <p class="text-[#ccff00] text-xs font-bold mt-1 tracking-widest uppercase">{{ $perfil->profesion }}</p>
                @endif
            </div>

            @if($perfil->bio)
                <p class="text-sm text-gray-400 text-center mb-8 italic">{{ $perfil->bio }}</p>
            @endif

            @if($perfil->telefono)
                <div class="w-full flex gap-3 mb-6">
                    <a href="tel:{{ $perfil->telefono }}" class="skew-btn h-12 bg-white w-1/2 flex items-center justify-center text-black font-black hover:bg-gray-200">
                        <span class="skew-content" x-text="t('LLAMAR', 'CALL NOW', 'LIGAR')"></span>
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $perfil->telefono) }}" class="skew-btn h-12 bg-[#ccff00] w-1/2 flex items-center justify-center text-black font-black hover:bg-[#aadd00]">
                        <span class="skew-content">WHATSAPP</span>
                    </a>
                </div>
            @endif

            <a href="/{{ $perfil->nickname }}/vcf" class="skew-btn h-14 mt-auto w-full bg-transparent border-2 border-[#ccff00] text-[#ccff00] font-black text-lg flex items-center justify-center hover:bg-[#ccff00] hover:text-black">
                <span class="skew-content uppercase tracking-wider" x-text="t('Guardar Contacto', 'Save Contact', 'Salvar Contato')"></span>
            </a>
        </div>
    </main>

    <script>
        function multidioma() {
            return {
                lang: navigator.language.slice(0, 2),
                t(es, en, pt) { return this.lang === 'es' ? es : (this.lang === 'pt' ? pt || es : en || es); }
            }
        }
    </script>
</body>
</html>