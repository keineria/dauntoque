<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $perfil->nombre }} | DaUnToque</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
        $designRaw = $perfil->design_config;
        $design = is_string($designRaw) ? json_decode($designRaw, true) : (array) $designRaw;
        
        $estructura = $design['layout_estructura'] ?? 'clasica';
        $formaBotones = $design['forma_botones'] ?? 'rounded-2xl';
        $temaBase = $design['tema_base'] ?? 'crystal';
        $colorPrimario = $design['color_primario'] ?? '#3b82f6';
        // Solo colores hexadecimales: evita inyectar CSS en el bloque <style>
        if (!is_string($colorPrimario) || !preg_match('/^#[0-9a-fA-F]{3,8}$/', $colorPrimario)) { $colorPrimario = '#3b82f6'; }

        // CONSTRUCTOR INTELIGENTE DE REDES SOCIALES
        function buildSocialUrl($network, $username) {
            $username = trim($username);
            // Si el cliente no hizo caso y pegó el link completo, lo dejamos pasar
            if(str_starts_with($username, 'http')) return $username;
            
            $username = ltrim($username, '@'); // Quitamos el @ si lo puso
            $domains = [
                'ig' => 'https://instagram.com/',
                'fb' => 'https://facebook.com/',
                'tk' => 'https://tiktok.com/@',
                'in' => 'https://linkedin.com/in/',
                'x'  => 'https://x.com/',
                'yt' => 'https://youtube.com/@',
            ];
            return ($domains[$network] ?? 'https://') . $username;
        }
    @endphp

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #ffffff; min-height: 100vh; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .btn-zelle { background-image: linear-gradient(to bottom, #8621a1, #741b8c); border: 1px solid #9127ad; color: white;}
        .btn-cashapp { background-color: #00D632; color: black; }
        .btn-venmo { background-color: #008CFF; color: white;}
        
        :root { --color-primario: {{ $colorPrimario }}; }

        /* Estilos de Plantillas */
        .tpl-crystal { background-image: linear-gradient(to bottom, rgba(9, 9, 11, 0.90), rgba(9, 9, 11, 0.98)), url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop'); background-size: cover; background-attachment: fixed;}
        .tpl-crystal .glass-card { background: rgba(20, 20, 25, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); }
        .tpl-crystal .accent-text { color: var(--color-primario); }
        .tpl-crystal .avatar-ring { border-color: #121216; }

        .tpl-matte { background-color: #09090b; }
        .tpl-matte .glass-card { background-color: #0d0d0d; border: 1px solid rgba(255,255,255,0.03); }
        .tpl-matte .accent-text { color: var(--color-primario); }
        .tpl-matte .avatar-ring { border-color: #27272a; }

        .tpl-luxury { background-color: #050505; }
        .tpl-luxury .glass-card { background-color: #121212; border: 1px solid var(--color-primario); }
        .tpl-luxury .accent-text { color: var(--color-primario); font-family: serif; }
        .tpl-luxury .avatar-ring { border-color: var(--color-primario); padding: 2px; }
        
        .tpl-corporativo { background-color: #f8fafc; color: #0f172a; }
        .tpl-corporativo .glass-card { background-color: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); }
        .tpl-corporativo .accent-text { color: var(--color-primario); font-family: serif; font-weight: 700;}
        .tpl-corporativo .avatar-ring { border-color: #e2e8f0; padding: 4px; background: white; }
        .tpl-corporativo p { color: #475569; }
        .tpl-corporativo h1, .tpl-corporativo h2 { color: #0f172a; }

        .tpl-sport { background-color: #111; color: white; background-image: radial-gradient(#1f2937 1px, transparent 1px); background-size: 20px 20px;}
        .tpl-sport .glass-card { background-color: #000; border-left: 4px solid var(--color-primario); box-shadow: 0 10px 30px rgba(0,0,0,0.8);}
        .tpl-sport .accent-text { color: var(--color-primario); font-weight: 900; letter-spacing: 1px;}
        .tpl-sport .avatar-ring { border-color: var(--color-primario); padding: 2px; border-width: 2px;}

        .tpl-minimalista { background-color: #fafafa; color: #111; }
        .tpl-minimalista .glass-card { background-color: #ffffff; border: 1px solid #eaeaea; }
        .tpl-minimalista .accent-text { color: var(--color-primario); }
        .tpl-minimalista .avatar-ring { border-color: #cccccc; padding: 2px; }
        .tpl-minimalista p { color: #666; }
        .tpl-minimalista h1, .tpl-minimalista h2 { color: #111; }
    </style>
</head>
<body class="tpl-{{ $temaBase }} flex justify-center">

    <main class="w-full max-w-md min-h-screen px-4 py-8 flex flex-col relative z-10" x-data="{ modalZelle: false }">
        
        <div class="glass-card rounded-3xl p-6 mb-6 flex-1 flex flex-col shadow-2xl">
            
            <!-- MODO FERIA -->
            @if($estructura == 'feria')
                <div class="w-full aspect-square mb-6 {{ $formaBotones }} overflow-hidden border-2 border-gray-800 shadow-lg">
                    @if($perfil->foto_perfil)
                        <img src="{{ $perfil->foto_perfil }}" class="w-full h-full object-cover">
                    @endif
                </div>

                <div class="text-center w-full mb-4">
                    <h1 class="text-3xl font-black tracking-tight leading-tight">{{ $perfil->nombre }}</h1>
                    @if($perfil->tipo_perfil !== 'empresa')
                        <h2 class="accent-text text-sm mt-1 uppercase tracking-widest">{{ $perfil->profesion }}</h2>
                    @endif
                </div>

                <a href="/{{ $linkMaestro }}/vcf" onclick="trackClick('descarga_vcf')" class="h-16 w-full bg-white text-black font-black text-lg flex items-center justify-center shadow-[0_0_25px_rgba(255,255,255,0.4)] hover:scale-105 transition-transform gap-2 mb-8 {{ $formaBotones }}">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> GUARDAR CONTACTO
                </a>

                @if($perfil->bio)
                    <div class="bg-black/10 p-4 border border-gray-500/20 mb-6 {{ $formaBotones }}">
                        <p class="text-sm font-light leading-relaxed text-center">{{ $perfil->bio }}</p>
                    </div>
                @endif

                @if(count($redes) > 0)
                    <h3 class="text-center text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Mis Redes Sociales</h3>
                    <div class="grid grid-cols-2 gap-3 mb-8">
                        @foreach($redes as $red)
                            <a href="{{ buildSocialUrl($red['id'], $red['usuario']) }}" target="_blank" onclick="trackClick('red_social_{{ $red['id'] }}')" class="h-10 flex items-center justify-center text-[10px] font-bold gap-2 bg-gray-900 border border-gray-800 shadow-sm text-white hover:bg-gray-800 transition-colors {{ $formaBotones }}">
                                {!! $red['svg'] ?? '🔗' !!} <span class="uppercase tracking-wider">{{ $red['nombre'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

            <!-- MODO CLÁSICO -->
            @else
                <div class="{{ ($perfil->alineacion == 'izquierda') ? 'flex flex-row items-center gap-5 mb-4 text-left' : 'flex flex-col items-center text-center' }}">
                    <div class="avatar-ring bg-gray-900 border-4 flex items-center justify-center shadow-xl overflow-hidden flex-shrink-0 {{ ($perfil->tipo_perfil == 'empresa') ? 'rounded-2xl' : 'rounded-full' }} {{ ($perfil->alineacion == 'izquierda') ? 'w-24 h-24 mb-0' : 'w-32 h-32 mb-5' }}">
                        @if($perfil->foto_perfil)
                            <img src="{{ $perfil->foto_perfil }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl text-gray-600 font-black">{{ substr($perfil->nombre, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="{{ ($perfil->alineacion == 'izquierda') ? 'flex flex-col flex-1' : 'flex flex-col items-center w-full' }}">
                        <h1 class="text-2xl font-bold tracking-tight">{{ $perfil->nombre }}</h1>
                        @if($perfil->tipo_perfil !== 'empresa')
                            <h2 class="accent-text text-xs mt-1 uppercase tracking-widest">{{ $perfil->profesion }}</h2>
                        @endif
                    </div>
                </div>

                @if($perfil->bio)
                    <p class="text-sm font-light mt-2 mb-8 leading-relaxed {{ ($perfil->alineacion == 'izquierda') ? 'text-left' : 'text-center' }}">
                        {{ $perfil->bio }}
                    </p>
                @endif

                @if(count($redes) > 0)
                    <!-- TAMAÑO DE BOTONES DE REDES REDUCIDO A h-10 Y TEXTO MAS PEQUEÑO -->
                    <div class="grid grid-cols-2 gap-3 mb-8">
                        @foreach($redes as $red)
                            <a href="{{ buildSocialUrl($red['id'], $red['usuario']) }}" target="_blank" onclick="trackClick('red_social_{{ $red['id'] }}')" class="h-10 flex items-center justify-center text-[10px] font-bold gap-2 bg-gray-900 text-white border border-gray-800 hover:bg-gray-800 transition-colors {{ $formaBotones }}">
                                <div class="w-4 h-4">{!! $red['svg'] ?? '🔗' !!}</div> <span class="uppercase tracking-wider">{{ $red['nombre'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif

            <!-- MÓDULOS COMPARTIDOS -->
            @if(count($galeria) > 0)
                <div class="grid grid-cols-2 gap-3 mb-8">
                    @foreach($galeria as $img)
                        <div class="relative w-full aspect-square overflow-hidden border border-gray-800 {{ $formaBotones }}">
                            <img src="{{ $img }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif

            @if($perfil->telefono)
                <div class="w-full flex gap-3 mb-6">
                    <a href="tel:{{ $perfil->telefono }}" onclick="trackClick('llamada')" class="h-12 bg-gray-800/50 border border-gray-700 w-1/2 flex items-center justify-center text-sm text-white font-bold gap-2 hover:bg-gray-700 {{ $formaBotones }}">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg> Llamar
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $perfil->telefono) }}" target="_blank" onclick="trackClick('whatsapp')" class="h-12 bg-[#25D366]/20 border border-[#25D366]/40 w-1/2 flex items-center justify-center text-sm text-[#4ade80] font-bold gap-2 hover:bg-[#25D366]/30 {{ $formaBotones }}">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg> WhatsApp
                    </a>
                </div>
            @endif

            @if($perfil->zelle || $perfil->cashapp)
                <div class="w-full space-y-3 mb-8">
                    @if($perfil->zelle)
                        <button @click="modalZelle = true" onclick="trackClick('zelle')" class="w-full h-14 btn-zelle flex items-center justify-center text-sm font-bold shadow-[0_10px_20px_rgba(134,33,161,0.2)] gap-2 {{ $formaBotones }}">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg> Pagar con Zelle
                        </button>
                    @endif
                    @if($perfil->cashapp)
                        <a href="https://cash.app/{{ $perfil->cashapp }}" target="_blank" onclick="trackClick('cashapp')" class="w-full h-14 btn-cashapp flex items-center justify-center text-sm font-bold {{ $formaBotones }}">Pagar con CashApp</a>
                    @endif
                </div>
            @endif

            @if($estructura !== 'feria')
                <a href="/{{ $linkMaestro }}/vcf" onclick="trackClick('descarga_vcf')" class="h-14 mt-auto w-full bg-blue-600 text-white font-black text-sm flex items-center justify-center shadow-[0_10px_25px_rgba(37,99,235,0.4)] hover:bg-blue-500 transition-colors gap-2 {{ $formaBotones }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Guardar Contacto
                </a>
            @endif
            
        </div>
        
        <a href="https://dauntoque.com" target="_blank" class="flex flex-col items-center justify-center text-gray-500 hover:text-gray-300 pb-4 transition-colors">
            <span class="text-[10px] font-semibold uppercase tracking-widest mb-1">Impulsado por</span>
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-blue-600 flex items-center justify-center font-bold text-white text-[10px]">D</div>
                <span class="font-extrabold text-sm tracking-tight">dauntoque</span>
            </div>
        </a>

        <!-- MODAL DE ZELLE -->
        <div x-show="modalZelle" class="fixed inset-0 bg-black/90 z-[9999] flex flex-col justify-end backdrop-blur-sm" style="display: none;">
            <div class="bg-[#121214] w-full rounded-t-2xl p-5 border-t border-gray-800 relative" @click.away="modalZelle = false">
                <button @click="modalZelle = false" class="absolute top-4 right-4 text-gray-500 hover:text-white text-2xl">&times;</button>
                <div class="flex items-center justify-center gap-2 text-white font-bold text-sm mb-4">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg> Zelle Transfer
                </div>
                <div class="bg-[#25d366]/10 border border-[#25d366]/40 rounded-xl p-3 mb-4 text-center">
                    <span class="text-[#f59e0b] font-black text-sm block mb-1">{{ $perfil->zelle }}</span>
                </div>
            </div>
        </div>

    </main>

    <!-- MOTOR DE ANALÍTICAS INVISIBLE -->
    <script>
        // Esta función dispara los eventos a la base de datos sin interrumpir al usuario
        function trackClick(tipoAccion) {
            fetch('/api/analiticas/registrar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    perfil_id: {{ $perfil->id }},
                    accion: tipoAccion
                })
            }).catch(() => {}); // Si falla, lo ignoramos para no dañar la experiencia
        }

        // Registrar la "Visita" cuando la página termina de cargar
        window.onload = function() {
            trackClick('vista_perfil');
        };
    </script>
</body>
</html>