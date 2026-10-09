<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $perfil->nombre }} | DaUnToque</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #ffffff; min-height: 100vh; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Colores Base */
        .btn-zelle { background-image: linear-gradient(to bottom, #8621a1, #741b8c); border: 1px solid #9127ad; color: white;}
        .btn-cashapp { background-color: #00D632; border: 1px solid #00ea36; color: black; }
        .btn-venmo { background-color: #008CFF; border: 1px solid #009dff; color: white;}
        
        /* PLANTILLAS EXACTAS DEL EDITOR */
        .tpl-crystal { background-image: linear-gradient(to bottom, rgba(9, 9, 11, 0.90), rgba(9, 9, 11, 0.98)), url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop'); background-size: cover; background-attachment: fixed;}
        .tpl-crystal .glass-card { background: rgba(20, 20, 25, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .tpl-crystal .accent-text { color: #60a5fa; }
        .tpl-crystal .avatar-ring { border-color: #121216; }

        .tpl-matte { background-color: #09090b; }
        .tpl-matte .glass-card { background-color: #0d0d0d; border: 1px solid rgba(255,255,255,0.03); box-shadow: inset 0 0 20px rgba(0,0,0,1); }
        .tpl-matte .accent-text { color: #9ca3af; }
        .tpl-matte .avatar-ring { border-color: #27272a; }

        .tpl-luxury { background-color: #050505; }
        .tpl-luxury .glass-card { background-color: #121212; border: 1px solid rgba(212, 175, 55, 0.2); box-shadow: 0 10px 30px rgba(0,0,0,0.8), inset 0 0 20px rgba(212, 175, 55, 0.05); }
        .tpl-luxury .accent-text { color: #D4AF37; }
        .tpl-luxury .avatar-ring { border-color: #D4AF37; padding: 2px; background: linear-gradient(135deg, #D4AF37 0%, #000 100%); }
    </style>
</head>
<body class="tpl-{{ $perfil->plantilla ?? 'crystal' }} flex justify-center">

    <main class="w-full max-w-md min-h-screen px-4 py-8 flex flex-col relative z-10" x-data="{ modalZelle: false }">
        
        <div class="glass-card rounded-3xl p-6 mb-6 flex-1 flex flex-col">
            
            <!-- ALINEACIÓN (Izquierda vs Centro) -->
            <div class="{{ ($perfil->alineacion == 'izquierda') ? 'flex flex-row items-center gap-5 mb-4 text-left' : 'flex flex-col items-center text-center' }}">
                
                <div class="avatar-ring bg-gradient-to-br from-gray-700 to-gray-900 border-4 flex items-center justify-center shadow-2xl overflow-hidden flex-shrink-0 {{ ($perfil->tipo_perfil == 'empresa') ? 'rounded-2xl' : 'rounded-full' }} {{ ($perfil->alineacion == 'izquierda') ? 'w-24 h-24 mb-0' : 'w-32 h-32 mb-5' }}">
                    @if($perfil->foto_perfil)
                        <img src="{{ $perfil->foto_perfil }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-4xl text-gray-600 font-black">{{ strtoupper(substr($perfil->nombre, 0, 1)) }}</span>
                    @endif
                </div>
                
                <div class="{{ ($perfil->alineacion == 'izquierda') ? 'flex flex-col flex-1' : 'flex flex-col items-center w-full' }}">
                    <h1 class="text-2xl font-bold text-white tracking-tight leading-tight">{{ $perfil->nombre }}</h1>
                    @if($perfil->tipo_perfil !== 'empresa')
                        <h2 class="accent-text text-xs font-bold mt-1 uppercase tracking-widest">{{ $perfil->profesion }}</h2>
                    @endif
                </div>
            </div>

            <!-- BIOGRAFÍA -->
            @if($perfil->bio)
                <p class="text-sm text-gray-300 font-light mt-2 mb-8 leading-relaxed {{ ($perfil->alineacion == 'izquierda') ? 'text-left' : 'text-center' }}">
                    {{ $perfil->bio }}
                </p>
            @endif

            <!-- GALERÍA DE FOTOS -->
            @if(count($galeria) > 0)
                <div class="grid grid-cols-2 gap-3 mb-8">
                    @foreach($galeria as $img)
                        <div class="relative w-full aspect-square overflow-hidden rounded-xl border border-gray-800 shadow-md">
                            <img src="{{ $img }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- BOTONES DIRECTOS (Llamada, WhatsApp) -->
            @if($perfil->telefono)
                <div class="w-full flex gap-3 mb-6">
                    <a href="tel:{{ $perfil->telefono }}" class="h-12 bg-gray-800/50 border border-gray-700 rounded-xl w-1/2 flex items-center justify-center text-sm text-white font-bold gap-2 hover:bg-gray-700 transition-colors">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg> Llamar
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $perfil->telefono) }}" target="_blank" class="h-12 bg-[#25D366]/20 border border-[#25D366]/40 rounded-xl w-1/2 flex items-center justify-center text-sm text-[#4ade80] font-bold gap-2 hover:bg-[#25D366]/30 transition-colors">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg> WhatsApp
                    </a>
                </div>
            @endif

            <!-- REDES SOCIALES (Mapeo inteligente de enlaces) -->
            @if(count($redes) > 0)
                <div class="grid grid-cols-2 gap-3 mb-8">
                    @foreach($redes as $red)
                        @php
                            $link = $red['usuario'];
                            if (!preg_match("~^(?:f|ht)tps?://~i", $link)) {
                                if (strtolower($red['nombre']) == 'instagram') $link = 'https://instagram.com/' . ltrim($link, '@');
                                elseif (strtolower($red['nombre']) == 'tiktok') $link = 'https://tiktok.com/@' . ltrim($link, '@');
                                elseif (strtolower($red['nombre']) == 'x (twitter)') $link = 'https://twitter.com/' . ltrim($link, '@');
                                else $link = 'https://' . $link;
                            }
                        @endphp
                        <a href="{{ $link }}" target="_blank" class="h-12 rounded-xl flex items-center justify-center text-xs font-bold gap-2 bg-gray-900 border border-gray-800 hover:border-gray-500 transition-colors shadow-sm">
                            {!! $red['svg'] ?? '🔗' !!} <span class="uppercase tracking-wider">{{ $red['nombre'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- PAGOS Y BILLETERAS -->
            <div class="w-full space-y-3 mb-8">
                @if($perfil->zelle)
                    <button @click="modalZelle = true" class="w-full h-14 rounded-xl btn-zelle flex items-center justify-center text-sm font-bold shadow-[0_10px_20px_rgba(134,33,161,0.2)] gap-2 hover:opacity-90 transition-opacity">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg> Pagar con Zelle
                    </button>
                @endif
                @if($perfil->cashapp)
                    <a href="https://cash.app/{{ $perfil->cashapp }}" target="_blank" class="w-full h-14 rounded-xl btn-cashapp flex items-center justify-center text-sm font-bold shadow-[0_10px_20px_rgba(0,214,50,0.15)] hover:opacity-90 transition-opacity">Pagar con CashApp</a>
                @endif
                @if($perfil->venmo)
                    <a href="https://venmo.com/{{ ltrim($perfil->venmo, '@') }}" target="_blank" class="w-full h-14 rounded-xl btn-venmo flex items-center justify-center text-sm font-bold shadow-[0_10px_20px_rgba(0,140,255,0.2)] hover:opacity-90 transition-opacity">Pagar con Venmo</a>
                @endif
            </div>

            <!-- DESCARGAR CONTACTO (Archivo .vcf) -->
            <a href="/{{ $perfil->nickname }}/vcf" class="h-14 mt-auto w-full bg-white text-black font-black text-sm rounded-xl flex items-center justify-center shadow-[0_0_25px_rgba(255,255,255,0.3)] hover:scale-[1.02] transition-transform gap-2">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Guardar Contacto
            </a>
            
        </div>

        <!-- VIRAL LOOP (Publicidad gratuita) -->
        <a href="https://dauntoque.com" target="_blank" class="flex flex-col items-center justify-center text-gray-500 hover:text-white transition-colors pb-4 opacity-70 hover:opacity-100">
            <span class="text-[10px] font-semibold uppercase tracking-widest mb-1">Impulsado por</span>
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center font-bold text-white text-[10px]">D</div>
                <span class="font-extrabold text-sm tracking-tight text-white">dauntoque</span>
            </div>
        </a>

        <!-- MODAL DE ZELLE -->
        <div x-show="modalZelle" x-transition.opacity class="fixed inset-0 bg-black/90 z-[100] flex flex-col justify-end backdrop-blur-md" style="display: none;">
            <div class="bg-[#121214] w-full max-w-md mx-auto rounded-t-3xl p-6 border-t border-gray-800 relative" @click.away="modalZelle = false">
                <button @click="modalZelle = false" class="absolute top-5 right-5 text-gray-500 hover:text-white text-xl">&times;</button>
                <div class="flex items-center justify-center gap-2 text-white font-bold text-lg mb-6 mt-2">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg> Detalles de Zelle
                </div>
                <p class="text-center text-gray-400 text-sm mb-2">Envía el pago a esta cuenta:</p>
                <div class="bg-gray-900 border border-gray-700 rounded-xl p-4 mb-6 text-center shadow-inner">
                    <span class="text-[#f59e0b] font-black text-xl block tracking-wide">{{ $perfil->zelle }}</span>
                </div>
            </div>
        </div>

    </main>
</body>
</html>