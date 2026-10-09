<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $perfil->nombre }} | DaUnToque</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    
    @php
        // Decodificamos el diseño para sacar la estructura. Si no existe, es 'clasica'.
        $design = json_decode($perfil->design_config, true) ?? [];
        $estructura = $design['layout_estructura'] ?? 'clasica';
        $formaBotones = $design['forma_botones'] ?? 'rounded-2xl';
    @endphp

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #ffffff; min-height: 100vh; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .btn-zelle { background-image: linear-gradient(to bottom, #8621a1, #741b8c); border: 1px solid #9127ad; color: white;}
        .btn-cashapp { background-color: #00D632; color: black; }
        .btn-venmo { background-color: #008CFF; color: white;}
        
        /* Variables CSS inyectadas por tu sistema modular */
        :root {
            --color-primario: {{ $design['color_primario'] ?? '#3b82f6' }};
        }

        /* TPL CRYSTAL */
        .tpl-crystal { background-image: linear-gradient(to bottom, rgba(9, 9, 11, 0.90), rgba(9, 9, 11, 0.98)), url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop'); background-size: cover; background-attachment: fixed;}
        .tpl-crystal .glass-card { background: rgba(20, 20, 25, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); }
        .tpl-crystal .accent-text { color: var(--color-primario); }
        .tpl-crystal .avatar-ring { border-color: #121216; }

        /* TPL MATTE */
        .tpl-matte { background-color: #09090b; }
        .tpl-matte .glass-card { background-color: #0d0d0d; border: 1px solid rgba(255,255,255,0.03); }
        .tpl-matte .accent-text { color: var(--color-primario); }
        .tpl-matte .avatar-ring { border-color: #27272a; }

        /* TPL LUXURY */
        .tpl-luxury { background-color: #050505; }
        .tpl-luxury .glass-card { background-color: #121212; border: 1px solid var(--color-primario); }
        .tpl-luxury .accent-text { color: var(--color-primario); font-family: serif; }
        .tpl-luxury .avatar-ring { border-color: var(--color-primario); padding: 2px; }
    </style>
</head>
<body class="tpl-{{ $design['tema_base'] ?? 'crystal' }} flex justify-center">

    <main class="w-full max-w-md min-h-screen px-4 py-8 flex flex-col relative z-10" x-data="{ modalZelle: false }">
        
        <div class="glass-card rounded-3xl p-6 mb-6 flex-1 flex flex-col shadow-2xl">
            
            <!-- ========================================== -->
            <!-- MODO FERIA (Impacto visual y guardado rápido) -->
            <!-- ========================================== -->
            @if($estructura == 'feria')
                
                <!-- Foto Gigante -->
                <div class="w-full aspect-square mb-6 {{ $formaBotones }} overflow-hidden border-2 border-gray-800 shadow-lg">
                    @if($perfil->foto_perfil)
                        <img src="{{ $perfil->foto_perfil }}" class="w-full h-full object-cover">
                    @endif
                </div>

                <div class="text-center w-full mb-4">
                    <h1 class="text-3xl font-black text-white tracking-tight leading-tight">{{ $perfil->nombre }}</h1>
                    @if($perfil->tipo_perfil !== 'empresa')
                        <h2 class="accent-text text-sm font-bold mt-1 uppercase tracking-widest">{{ $perfil->profesion }}</h2>
                    @endif
                </div>

                <!-- Botón Guardar Contacto (GIGANTE INMEDIATO) -->
                <a href="/{{ $perfil->nickname }}/vcf" class="h-16 w-full bg-white text-black font-black text-lg flex items-center justify-center shadow-[0_0_25px_rgba(255,255,255,0.4)] hover:scale-105 transition-transform gap-2 mb-8 {{ $formaBotones }}">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> GUARDAR CONTACTO
                </a>

                @if($perfil->bio)
                    <div class="bg-black/30 p-4 border border-white/5 mb-6 {{ $formaBotones }}">
                        <p class="text-sm text-gray-300 font-light leading-relaxed text-center">{{ $perfil->bio }}</p>
                    </div>
                @endif

                <!-- Título Redes Sociales -->
                @if(count($redes) > 0)
                    <h3 class="text-center text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Mis Redes Sociales</h3>
                    <div class="grid grid-cols-2 gap-3 mb-8">
                        @foreach($redes as $red)
                            <a href="{{ $red['usuario'] }}" target="_blank" class="h-12 flex items-center justify-center text-xs font-bold gap-2 bg-gray-900 border border-gray-800 shadow-sm {{ $formaBotones }}">
                                {!! $red['svg'] ?? '🔗' !!} <span class="uppercase tracking-wider">{{ $red['nombre'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

            <!-- ========================================== -->
            <!-- MODO CLÁSICO O DEFAULT -->
            <!-- ========================================== -->
            @else
                <!-- Alineación Clásica (Izquierda o Centro) -->
                <div class="{{ ($perfil->alineacion == 'izquierda') ? 'flex flex-row items-center gap-5 mb-4 text-left' : 'flex flex-col items-center text-center' }}">
                    <div class="avatar-ring bg-gray-900 border-4 flex items-center justify-center shadow-xl overflow-hidden flex-shrink-0 {{ ($perfil->tipo_perfil == 'empresa') ? 'rounded-2xl' : 'rounded-full' }} {{ ($perfil->alineacion == 'izquierda') ? 'w-24 h-24 mb-0' : 'w-32 h-32 mb-5' }}">
                        @if($perfil->foto_perfil)
                            <img src="{{ $perfil->foto_perfil }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl text-gray-600 font-black">{{ substr($perfil->nombre, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="{{ ($perfil->alineacion == 'izquierda') ? 'flex flex-col flex-1' : 'flex flex-col items-center w-full' }}">
                        <h1 class="text-2xl font-bold text-white tracking-tight">{{ $perfil->nombre }}</h1>
                        @if($perfil->tipo_perfil !== 'empresa')
                            <h2 class="accent-text text-xs font-bold mt-1 uppercase tracking-widest">{{ $perfil->profesion }}</h2>
                        @endif
                    </div>
                </div>

                @if($perfil->bio)
                    <p class="text-sm text-gray-300 font-light mt-2 mb-8 leading-relaxed {{ ($perfil->alineacion == 'izquierda') ? 'text-left' : 'text-center' }}">
                        {{ $perfil->bio }}
                    </p>
                @endif

                @if(count($redes) > 0)
                    <div class="grid grid-cols-2 gap-3 mb-8">
                        @foreach($redes as $red)
                            <a href="{{ $red['usuario'] }}" target="_blank" class="h-12 flex items-center justify-center text-xs font-bold gap-2 bg-gray-900 border border-gray-800 {{ $formaBotones }}">
                                {!! $red['svg'] ?? '🔗' !!} <span class="uppercase tracking-wider">{{ $red['nombre'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif

            <!-- MODULOS COMPARTIDOS POR TODAS LAS ESTRUCTURAS -->
            <!-- GALERÍA DE FOTOS -->
            @if(count($galeria) > 0)
                <div class="grid grid-cols-2 gap-3 mb-8">
                    @foreach($galeria as $img)
                        <div class="relative w-full aspect-square overflow-hidden border border-gray-800 {{ $formaBotones }}">
                            <img src="{{ $img }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- BOTONES DIRECTOS (Llamada, WhatsApp) -->
            @if($perfil->telefono)
                <div class="w-full flex gap-3 mb-6">
                    <a href="tel:{{ $perfil->telefono }}" class="h-12 bg-gray-800/50 border border-gray-700 w-1/2 flex items-center justify-center text-sm text-white font-bold gap-2 hover:bg-gray-700 {{ $formaBotones }}">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg> Llamar
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $perfil->telefono) }}" target="_blank" class="h-12 bg-[#25D366]/20 border border-[#25D366]/40 w-1/2 flex items-center justify-center text-sm text-[#4ade80] font-bold gap-2 hover:bg-[#25D366]/30 {{ $formaBotones }}">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg> WhatsApp
                    </a>
                </div>
            @endif

            <!-- PAGOS Y BILLETERAS -->
            <div class="w-full space-y-3 mb-8">
                @if($perfil->zelle)
                    <button @click="modalZelle = true" class="w-full h-14 btn-zelle flex items-center justify-center text-sm font-bold shadow-[0_10px_20px_rgba(134,33,161,0.2)] gap-2 {{ $formaBotones }}">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg> Pagar con Zelle
                    </button>
                @endif
                @if($perfil->cashapp)
                    <a href="https://cash.app/{{ $perfil->cashapp }}" target="_blank" class="w-full h-14 btn-cashapp flex items-center justify-center text-sm font-bold {{ $formaBotones }}">Pagar con CashApp</a>
                @endif
            </div>

            <!-- Botón de Descarga Clásico (Si no es Feria) -->
            @if($estructura !== 'feria')
                <a href="/{{ $perfil->nickname }}/vcf" class="h-14 mt-auto w-full bg-white text-black font-black text-sm flex items-center justify-center shadow-[0_0_25px_rgba(255,255,255,0.3)] hover:scale-[1.02] transition-transform gap-2 {{ $formaBotones }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Guardar Contacto
                </a>
            @endif
            
        </div>
        
        <!-- Viral Loop -->
        <a href="https://dauntoque.com" target="_blank" class="flex flex-col items-center justify-center text-gray-500 hover:text-white pb-4 opacity-70">
            <span class="text-[10px] font-semibold uppercase tracking-widest mb-1">Impulsado por</span>
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-blue-600 flex items-center justify-center font-bold text-white text-[10px]">D</div>
                <span class="font-extrabold text-sm tracking-tight text-white">dauntoque</span>
            </div>
        </a>

        <!-- MODAL DE ZELLE (Oculto) -->
        <!-- (El modal se mantiene igual, no lo reescribo para ahorrar espacio) -->
    </main>
</body>
</html>