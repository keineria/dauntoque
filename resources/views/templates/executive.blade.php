<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $perfil->nombre }} | DaUnToque</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        h1, h2 { font-family: 'Playfair Display', serif; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .executive-card { background: white; border: 1px solid #e2e8f0; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="flex justify-center min-h-screen" x-data="multidioma()">
    <main class="w-full max-w-md px-4 py-8 flex flex-col relative z-10">
        
        <div class="executive-card rounded-2xl p-8 mb-6 flex-1 flex flex-col">
            <!-- Cabecera -->
            <div class="{{ ($perfil->alineacion == 'izquierda') ? 'flex flex-row items-center gap-5 mb-6 text-left' : 'flex flex-col items-center text-center mb-6' }}">
                <div class="border border-gray-200 p-1 flex items-center justify-center bg-white shadow-sm flex-shrink-0 {{ ($perfil->tipo_perfil == 'empresa') ? 'rounded-xl' : 'rounded-full' }} {{ ($perfil->alineacion == 'izquierda') ? 'w-20 h-20' : 'w-28 h-28 mb-4' }}">
                    @if($perfil->foto_perfil)
                        <img src="{{ $perfil->foto_perfil }}" class="w-full h-full object-cover {{ ($perfil->tipo_perfil == 'empresa') ? 'rounded-lg' : 'rounded-full' }}">
                    @endif
                </div>
                <div class="{{ ($perfil->alineacion == 'izquierda') ? 'flex flex-col flex-1' : 'flex flex-col items-center w-full' }}">
                    <h1 class="text-2xl font-bold text-slate-900 leading-tight">{{ $perfil->nombre }}</h1>
                    @if($perfil->tipo_perfil !== 'empresa')
                        <p class="text-sm font-medium text-slate-500 mt-1 uppercase tracking-wide">{{ $perfil->profesion }}</p>
                    @endif
                </div>
            </div>

            @if($perfil->bio)
                <p class="text-sm text-slate-600 leading-relaxed mb-8 {{ ($perfil->alineacion == 'izquierda') ? 'text-left' : 'text-center' }}">{{ $perfil->bio }}</p>
            @endif

            <!-- Botones de Contacto Multidioma -->
            @if($perfil->telefono)
                <div class="w-full flex gap-3 mb-6">
                    <a href="tel:{{ $perfil->telefono }}" class="h-12 bg-slate-900 rounded-lg w-1/2 flex items-center justify-center text-sm text-white font-medium hover:bg-slate-800 transition-colors shadow-md">
                        📞 <span x-text="t('Llamar', 'Call', 'Ligar')"></span>
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $perfil->telefono) }}" class="h-12 bg-[#25D366]/10 border border-[#25D366]/20 rounded-lg w-1/2 flex items-center justify-center text-sm text-[#15803d] font-medium hover:bg-[#25D366]/20 transition-colors">
                        WhatsApp
                    </a>
                </div>
            @endif

            <!-- VCF Descarga -->
            <a href="/{{ $perfil->nickname }}/vcf" class="h-14 mt-auto w-full bg-blue-600 text-white font-semibold text-sm rounded-lg flex items-center justify-center shadow-[0_10px_20px_rgba(37,99,235,0.2)] hover:bg-blue-700 transition-colors gap-2">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> 
                <span x-text="t('Guardar Contacto', 'Save Contact', 'Salvar Contato')"></span>
            </a>
        </div>
    </main>

    <script>
        function multidioma() {
            return {
                lang: navigator.language.slice(0, 2),
                t(es, en, pt) {
                    if (this.lang === 'es') return es;
                    if (this.lang === 'pt') return pt || es;
                    return en || es;
                }
            }
        }
    </script>
</body>
</html>