<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DaUnToque - Creador de Perfil IA</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #050505; 
            color: #ffffff; 
            overflow: hidden; 
            overscroll-behavior: none; 
        }
        .glass-panel { 
            background: rgba(20, 20, 25, 0.7); 
            backdrop-filter: blur(25px); 
            -webkit-backdrop-filter: blur(25px); 
            border: 1px solid rgba(255, 255, 255, 0.05); 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
        }
        .glow-accent { 
            box-shadow: 0 0 20px rgba(37, 99, 235, 0.3); 
        }
        
        /* 📱 AJUSTES EXCLUSIVOS PARA MÓVIL */
        @media (max-width: 768px) {
            .mobile-fullscreen { height: 100dvh; display: flex; flex-direction: column; }
            .mobile-content-area { flex: 1; overflow-y: auto; padding-bottom: 100px; }
            .mobile-fixed-bottom { 
                position: fixed; bottom: 0; left: 0; width: 100%; 
                background: #050505; border-top: 1px solid #1f2937; 
                padding: 15px; z-index: 40; display: flex; gap: 10px; justify-content: space-between; 
            }
        }

        .phone-mockup { 
            width: 320px; height: 650px; border: 12px solid #1a1a1c; border-radius: 40px; 
            background-color: #09090b; position: relative; overflow: hidden; 
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.8), inset 0 0 15px rgba(255,255,255,0.05); 
        }
        .phone-notch { 
            position: absolute; top: 0; left: 50%; transform: translateX(-50%); 
            width: 120px; height: 25px; background-color: #1a1a1c; 
            border-bottom-left-radius: 15px; border-bottom-right-radius: 15px; z-index: 50; 
        }
        
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .btn-zelle { background-image: linear-gradient(to bottom, #8621a1, #741b8c); border: 1px solid #9127ad; color: white;}
        .btn-cashapp { background-color: #00D632; border: 1px solid #00ea36; color: black; }
        .btn-venmo { background-color: #008CFF; border: 1px solid #009dff; color: white;}
        
        /* ====================================================
           SISTEMA DE PLANTILLAS MODULARES (LIVE PREVIEW)
        ==================================================== */
        .tpl-crystal { 
            background: rgba(20, 20, 25, 0.4); backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); 
            border-radius: 24px; padding: 20px 15px; margin: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
        }
        .tpl-crystal .accent-text { color: var(--color-primario, #3b82f6); }
        .tpl-crystal .avatar-ring { border-color: #121216; }

        .tpl-matte { 
            background-color: #0d0d0d; border: 1px solid rgba(255,255,255,0.03); 
            border-radius: 24px; padding: 20px 15px; margin: 15px; box-shadow: inset 0 0 20px rgba(0,0,0,1); 
        }
        .tpl-matte .accent-text { color: var(--color-primario, #9ca3af); }
        .tpl-matte .avatar-ring { border-color: #27272a; }

        .tpl-luxury { 
            background-color: #121212; border: 1px solid var(--color-primario, #D4AF37); 
            border-radius: 24px; padding: 20px 15px; margin: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.8), inset 0 0 20px rgba(212, 175, 55, 0.05); 
        }
        .tpl-luxury .accent-text { color: var(--color-primario, #D4AF37); font-family: serif; }
        .tpl-luxury .avatar-ring { border-color: var(--color-primario, #D4AF37); padding: 2px; }
        .tpl-luxury h2 { font-family: serif; }

        .tpl-corporativo { 
            background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; 
            padding: 20px 15px; margin: 15px; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); 
        }
        .tpl-corporativo h2 { color: #0f172a; font-family: serif; font-weight: 700; }
        .tpl-corporativo p { color: #475569; }
        .tpl-corporativo .accent-text { color: var(--color-primario, #0f172a); }
        .tpl-corporativo .avatar-ring { border-color: #e2e8f0; background: white; padding: 4px; }

        .tpl-sport { 
            background-color: #111; border-left: 4px solid var(--color-primario, #ccff00); 
            border-radius: 12px; padding: 20px 15px; margin: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.8); 
        }
        .tpl-sport h2 { color: white; text-transform: uppercase; letter-spacing: 1px; font-weight: 900; font-family: monospace; }
        .tpl-sport p { color: #9ca3af; }
        .tpl-sport .accent-text { color: var(--color-primario, #ccff00); }
        .tpl-sport .avatar-ring { border-color: var(--color-primario, #ccff00); border-width: 2px; padding: 2px; box-shadow: 0 0 15px rgba(204,255,0,0.2); }

        .tpl-minimalista { 
            background-color: #fafafa; border: 1px solid #eaeaea; border-radius: 20px; 
            padding: 20px 15px; margin: 15px; 
        }
        .tpl-minimalista h2 { color: #111111; font-weight: 600; }
        .tpl-minimalista p { color: #666666; font-weight: 300; }
        .tpl-minimalista .accent-text { color: var(--color-primario, #111111); }
        .tpl-minimalista .avatar-ring { border-color: #cccccc; padding: 2px; }
    </style>
</head>
<body class="h-screen w-screen flex flex-col md:flex-row relative mobile-fullscreen" x-data="perfilDigital()">

    <!-- NOTIFICACIÓN DE ERROR GENERAL (Fallos de servidor, etc) -->
    <div x-show="toastError !== ''" x-transition.opacity style="display: none;" class="fixed top-6 left-1/2 transform -translate-x-1/2 z-[9999] bg-[#ffffff] rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.5)] p-5 w-[90%] max-w-sm border border-gray-300 flex flex-col gap-3">
        <p class="text-black font-bold text-sm">portal.dauntoque.com dice</p>
        <div class="flex items-start gap-2 text-gray-700 text-sm">
            <span class="text-yellow-500 text-lg leading-none">⚠️</span>
            <span x-text="toastError"></span>
        </div>
        <div class="flex justify-end mt-2">
            <button @click="toastError = ''" class="bg-[#3b5998] hover:bg-[#2d4373] text-white font-bold py-2 px-6 rounded-full border-2 border-[#8b9dc3] shadow-md transition-colors">Aceptar</button>
        </div>
    </div>

    <!-- Luces de fondo -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-20 animate-pulse pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-20 animate-pulse pointer-events-none"></div>

    <!-- MITAD IZQUIERDA: Formularios de Gestión -->
    <div class="w-full md:w-1/2 h-full flex flex-col relative z-10 overflow-y-auto hide-scrollbar mobile-content-area pb-24 md:pb-0">
        
        <!-- CONTENEDOR AGRUPADO Y CENTRADO -->
        <div class="w-full max-w-lg mx-auto my-auto py-10 px-6 md:px-8 flex flex-col gap-4 transition-all duration-500 relative">
            
            <!-- BARRA DE PROGRESO -->
            <div class="w-full">
                <div class="flex justify-between items-end mb-2">
                    <h2 class="text-xl md:text-2xl font-black text-white" x-text="titulos[paso]"></h2>
                    <span class="text-xl md:text-3xl font-black text-blue-500" x-text="`${progreso}%`"></span>
                </div>
                <p class="text-xs text-gray-400 mb-4" x-text="subtitulos[paso]"></p>
                <div class="w-full h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-600 to-purple-500 transition-all duration-500" :style="`width: ${progreso}%`"></div>
                </div>
            </div>

            <!-- GLASS PANEL (Formulario) -->
            <div class="glass-panel p-6 md:p-8 rounded-3xl w-full relative">
                
                <!-- PASO 1: Nickname (SOLO CREACIÓN) -->
                <div x-show="paso === 1 && modoActual === 'crear'" x-transition.opacity.duration.300ms>
                    <label class="block text-sm font-semibold mb-2 text-gray-300">Crea tu usuario único</label>
                    <div class="flex items-center gap-3 mb-2">
                        <input type="text" x-model="nickname" @input="nickValidado = false; nickError = ''" placeholder="ej. carlos" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 outline-none lowercase focus:border-blue-500 transition-colors">
                        
                        <button @click="validarNickname" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-xl transition-colors whitespace-nowrap flex items-center gap-2">
                            <span x-show="!validandoNick">Validar</span>
                            <span x-show="validandoNick" class="animate-pulse">...</span>
                        </button>
                    </div>
                    <!-- Alertas silenciosas en línea -->
                    <p x-show="nickValidado" class="text-green-400 text-xs font-bold ml-2 mb-2">✓ Usuario disponible</p>
                    <p x-show="nickError !== ''" class="text-red-500 text-xs font-bold ml-2 mb-2" x-text="nickError"></p>
                </div>

                <!-- PASO 2: Tipo y Foto -->
                <div x-show="paso === 2" x-transition.opacity.duration.300ms style="display: none;">
                    <label class="block text-sm font-semibold mb-2 text-gray-300">¿Qué uso le darás a tu tarjeta?</label>
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <button @click="tipoPerfil = 'corporativo'" :class="tipoPerfil === 'corporativo' ? 'bg-blue-600 text-white' : 'bg-gray-900 text-gray-400'" class="border border-gray-700 py-2 rounded-lg text-xs font-bold transition-colors">Corporativo</button>
                        <button @click="tipoPerfil = 'empresa'" :class="tipoPerfil === 'empresa' ? 'bg-blue-600 text-white' : 'bg-gray-900 text-gray-400'" class="border border-gray-700 py-2 rounded-lg text-xs font-bold transition-colors">Empresa</button>
                        <button @click="tipoPerfil = 'hobbies'" :class="tipoPerfil === 'hobbies' ? 'bg-blue-600 text-white' : 'bg-gray-900 text-gray-400'" class="border border-gray-700 py-2 rounded-lg text-xs font-bold transition-colors">Hobbies</button>
                    </div>

                    <label class="w-full border-2 border-dashed border-gray-700 rounded-xl p-4 flex flex-col items-center justify-center mb-6 cursor-pointer hover:border-blue-500 transition-colors relative overflow-hidden">
                        <input type="file" accept="image/*" @change="abrirRecorte" class="hidden">
                        <div x-show="!fotoPreview" class="flex flex-col items-center pointer-events-none">
                            <svg class="w-6 h-6 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span class="text-xs text-gray-400">Subir Foto</span>
                        </div>
                        <div x-show="fotoPreview" class="w-full flex justify-center pointer-events-none" style="display: none;">
                            <img :src="fotoPreview" class="h-20 w-20 object-cover rounded-full border-2 border-blue-500">
                        </div>
                    </label>
                    <label class="block text-sm font-semibold mb-2 text-gray-300">Tu Nombre Público</label>
                    <input type="text" x-model="nombre" placeholder="Tu Nombre Completo" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 mb-6 text-white outline-none focus:border-blue-500">
                </div>

                <!-- PASO 3: Profesión -->
                <div x-show="paso === 3" x-transition.opacity.duration.300ms style="display: none;">
                    <label class="block text-sm font-semibold mb-2 text-gray-300">Título o Profesión</label>
                    <input type="text" x-model="profesion" placeholder="ej. Consultor de Negocios" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 mb-6 text-white outline-none focus:border-blue-500">
                </div>

                <!-- PASO 4: Biografía + Inteligencia Artificial -->
                <div x-show="paso === 4" x-transition.opacity.duration.300ms style="display: none;">
                    <label class="block text-sm font-semibold mb-2 text-gray-300">Descripción del Perfil</label>
                    <textarea x-model="bio" rows="4" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 mb-4 text-white outline-none focus:border-blue-500 resize-none" placeholder="Escribe a qué te dedicas o usa la IA..."></textarea>
                    
                    <div class="flex justify-end">
                        <button @click="mejorarConIA" :disabled="generandoIA" class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white text-xs font-bold py-2 px-5 rounded-xl flex items-center gap-2 transition-all shadow-[0_0_15px_rgba(147,51,234,0.3)] disabled:opacity-50">
                            <svg x-show="!generandoIA" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span x-show="!generandoIA">✨ Mejorar con IA</span>
                            <span x-show="generandoIA" class="animate-pulse">Pensando...</span>
                        </button>
                    </div>
                </div>

                <!-- PASO 5: Galería -->
                <div x-show="paso === 5" x-transition.opacity.duration.300ms style="display: none;">
                    <label class="block text-sm font-semibold mb-2 text-gray-300">Portafolio / Productos (Máx 4)</label>
                    <div class="w-full border-2 border-dashed border-gray-700 rounded-xl p-6 flex flex-col items-center justify-center mb-4 cursor-pointer hover:border-blue-500 relative" x-show="galeriaPreviews.length < 4">
                        <input type="file" multiple accept="image/*" @change="procesarImagenesGaleria($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <span class="text-xs text-gray-400 text-center">Cargar imágenes<br>(<span x-text="4 - galeriaPreviews.length"></span> disponibles)</span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 mb-6" x-show="galeriaPreviews.length > 0">
                        <template x-for="(img, index) in galeriaPreviews" :key="index">
                            <div class="relative group">
                                <img :src="img" class="w-full aspect-square object-cover rounded-lg border border-gray-700">
                                <button @click="eliminarImagenGaleria(index)" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100">&times;</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PASO 6: Contacto y Redes -->
                <div x-show="paso === 6" x-transition.opacity.duration.300ms style="display: none;">
                    <div class="mb-6 space-y-3">
                        <div class="flex items-center bg-black border border-gray-800 rounded-xl overflow-hidden focus-within:border-blue-500">
                            <span class="pl-4 text-gray-500">📞</span>
                            <input type="tel" x-model="telefono" placeholder="Teléfono / WhatsApp" class="w-full bg-transparent text-white px-4 py-3 outline-none">
                        </div>
                        <div class="flex items-center bg-black border border-gray-800 rounded-xl overflow-hidden focus-within:border-blue-500">
                            <span class="pl-4 text-gray-500">✉️</span>
                            <input type="email" x-model="correo" placeholder="Correo Electrónico" class="w-full bg-transparent text-white px-4 py-3 outline-none">
                        </div>
                        <div class="flex items-center bg-black border border-gray-800 rounded-xl overflow-hidden focus-within:border-blue-500">
                            <span class="pl-4 text-gray-500">📍</span>
                            <input type="text" x-model="direccion" placeholder="Dirección (Opcional)" class="w-full bg-transparent text-white px-4 py-3 outline-none">
                        </div>
                    </div>

                    <label class="block text-sm font-semibold mb-2 text-gray-300 flex justify-between">
                        Redes Sociales <span class="text-xs text-blue-400"><span x-text="redesSeleccionadas.length"></span>/4</span>
                    </label>
                    
                    <div class="grid grid-cols-4 gap-2 mb-4" :class="redesSeleccionadas.length >= 4 ? 'opacity-50 pointer-events-none' : ''">
                        <template x-for="red in opcionesRedes" :key="red.id">
                            <button @click="agregarRed(red)" x-show="!redesSeleccionadas.find(r => r.id === red.id)" class="bg-gray-900 border border-gray-700 hover:border-blue-500 rounded-lg py-2 text-xs font-bold transition-all flex flex-col items-center gap-1 text-gray-400 hover:text-white">
                                <div x-html="red.svg" class="w-5 h-5"></div>
                                <span x-text="red.nombre" class="text-[9px] uppercase tracking-wider"></span>
                            </button>
                        </template>
                    </div>
                    
                    <p class="text-[10px] text-gray-500 mb-3" x-show="redesSeleccionadas.length > 0">⚠️ Ingresa solo tu nombre de usuario (ej. carlos_123), el sistema creará el link automáticamente.</p>
                    
                    <div class="space-y-3 mb-6">
                        <template x-for="(red, index) in redesSeleccionadas" :key="index">
                            <div class="flex items-center bg-black border border-gray-800 rounded-xl overflow-hidden focus-within:border-blue-500">
                                <div class="pl-4 text-gray-400" x-html="red.svg"></div>
                                <input type="text" x-model="red.usuario" :placeholder="'Solo tu usuario de ' + red.nombre" class="w-full bg-transparent text-white px-4 py-3 outline-none text-sm">
                                <button @click="quitarRed(index)" class="px-4 text-red-500 font-bold">&times;</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PASO 7: Billeteras -->
                <div x-show="paso === 7" x-transition.opacity.duration.300ms style="display: none;">
                    <label class="block text-sm font-semibold mb-4 text-gray-300">Billeteras Digitales (Opcional)</label>
                    <div class="space-y-4 mb-6">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><div class="w-6 h-6 rounded bg-[#741b8c] flex items-center justify-center text-white font-bold text-xs">Z</div></div>
                            <input type="text" x-model="zelle" placeholder="Correo o Teléfono de Zelle" class="w-full bg-black border border-gray-800 rounded-xl pl-12 pr-4 py-3 text-white outline-none focus:border-[#a855f7]">
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><div class="w-6 h-6 rounded bg-[#00D632] flex items-center justify-center text-black font-bold text-xs">$</div></div>
                            <input type="text" x-model="cashapp" placeholder="$Cashtag de CashApp" class="w-full bg-black border border-gray-800 rounded-xl pl-12 pr-4 py-3 text-white outline-none focus:border-[#00D632]">
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><div class="w-6 h-6 rounded bg-[#008CFF] flex items-center justify-center text-white font-bold text-xs">V</div></div>
                            <input type="text" x-model="venmo" placeholder="@Usuario de Venmo" class="w-full bg-black border border-gray-800 rounded-xl pl-12 pr-4 py-3 text-white outline-none focus:border-[#008CFF]">
                        </div>
                    </div>
                </div>

                <!-- PASO 8: DISEÑO (Sistema Modular) -->
                <div x-show="paso === 8" x-transition.opacity.duration.300ms style="display: none;">
                    
                    <label class="block text-xs font-bold mb-3 text-gray-400 uppercase tracking-widest">1. Estructura y Plantillas</label>
                    <div class="flex overflow-x-auto gap-3 pb-4 hide-scrollbar mb-4">
                        <button @click="aplicarPreset('crystal')" :class="config.tema_base === 'crystal' && config.layout_estructura !== 'feria' ? 'ring-2 ring-blue-500 scale-105' : 'opacity-70'" class="w-24 h-32 flex-shrink-0 bg-blue-900/40 border border-white/20 rounded-xl flex flex-col items-center justify-center backdrop-blur-md transition-all">
                            <span class="text-white font-sans font-bold text-xs">CRYSTAL</span>
                        </button>
                        <button @click="aplicarPreset('feria')" :class="config.layout_estructura === 'feria' ? 'ring-2 ring-red-500 scale-105' : 'opacity-70'" class="w-24 h-32 flex-shrink-0 bg-black border border-red-500 rounded-xl flex flex-col items-center justify-center transition-all">
                            <span class="text-red-500 font-sans font-black text-xs uppercase text-center leading-tight">MODO<br>FERIA</span>
                        </button>
                        <button @click="aplicarPreset('luxury')" :class="config.tema_base === 'luxury' && config.layout_estructura !== 'feria' ? 'ring-2 ring-yellow-500 scale-105' : 'opacity-70'" class="w-24 h-32 flex-shrink-0 bg-[#050505] border-2 border-[#D4AF37] rounded-xl flex flex-col items-center justify-center transition-all">
                            <span class="text-[#D4AF37] font-serif font-bold text-xs">LUXURY</span>
                        </button>
                        <button @click="aplicarPreset('sport')" :class="config.tema_base === 'sport' && config.layout_estructura !== 'feria' ? 'ring-2 ring-green-500 scale-105' : 'opacity-70'" class="w-24 h-32 flex-shrink-0 bg-black border-l-4 border-[#ccff00] rounded-xl flex flex-col items-center justify-center transition-all">
                            <span class="text-[#ccff00] font-mono font-bold text-xs uppercase italic">SPORT</span>
                        </button>
                        <button @click="aplicarPreset('corporativo')" :class="config.tema_base === 'corporativo' && config.layout_estructura !== 'feria' ? 'ring-2 ring-blue-400 scale-105' : 'opacity-70'" class="w-24 h-32 flex-shrink-0 bg-white border border-gray-300 rounded-xl flex flex-col items-center justify-center transition-all">
                            <span class="text-slate-800 font-serif font-bold text-xs">CORP</span>
                        </button>
                    </div>

                    <hr class="border-gray-800 mb-6">

                    <label class="block text-xs font-bold mb-3 text-gray-400 uppercase tracking-widest">2. Ajuste Fino</label>
                    <div class="space-y-4">
                        <div class="flex gap-2 bg-black p-1 rounded-xl border border-gray-800" x-show="config.layout_estructura !== 'feria'">
                            <button @click="config.alineacion = 'centro'; alineacion = 'centro';" :class="config.alineacion === 'centro' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-300'" class="flex-1 py-2 rounded-lg text-xs font-bold transition-all">
                                Centro
                            </button>
                            <button @click="config.alineacion = 'izquierda'; alineacion = 'izquierda';" :class="config.alineacion === 'izquierda' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-300'" class="flex-1 py-2 rounded-lg text-xs font-bold transition-all">
                                Izquierda
                            </button>
                        </div>

                        <div class="flex items-center justify-between bg-black p-3 rounded-xl border border-gray-800">
                            <span class="text-sm text-gray-300">Color Primario (Acentos)</span>
                            <input type="color" x-model="config.color_primario" class="w-8 h-8 rounded cursor-pointer bg-transparent border-none">
                        </div>

                        <div class="flex gap-2 bg-black p-1 rounded-xl border border-gray-800">
                            <button @click="config.forma_botones = 'rounded-none'" :class="config.forma_botones === 'rounded-none' ? 'bg-gray-800 text-white' : 'text-gray-500'" class="flex-1 py-2 rounded-lg text-xs">Cuadrado</button>
                            <button @click="config.forma_botones = 'rounded-xl'" :class="config.forma_botones === 'rounded-xl' ? 'bg-gray-800 text-white' : 'text-gray-500'" class="flex-1 py-2 rounded-lg text-xs">Suave</button>
                            <button @click="config.forma_botones = 'rounded-full'" :class="config.forma_botones === 'rounded-full' ? 'bg-gray-800 text-white' : 'text-gray-500'" class="flex-1 py-2 rounded-lg text-xs">Píldora</button>
                        </div>
                    </div>
                </div>

                <!-- PASO 9: BAUTIZAR PERFIL -->
                <div x-show="paso === 9" x-transition.opacity.duration.300ms style="display: none;">
                    <label class="block text-sm font-semibold mb-2 text-gray-300">Bautiza esta configuración</label>
                    <p class="text-xs text-gray-500 mb-6">Dale un nombre para identificar este diseño en tu panel (ej. "Perfil Fin de Semana", "Perfil Ejecutivo").</p>
                    <div class="flex items-center mb-6 bg-black border border-gray-800 rounded-xl overflow-hidden focus-within:border-blue-500 transition-colors">
                        <input type="text" x-model="nombrePerfil" placeholder="Mi Tarjeta Principal" class="w-full bg-transparent text-white px-4 py-3 outline-none text-sm">
                    </div>
                </div>

            </div>
        </div>

        <!-- 📱 BOTONERA INFERIOR FIJA (Móvil) / ESTÁNDAR (PC) -->
        <div class="mobile-fixed-bottom md:static md:px-8 md:pb-8 md:pt-4 md:border-none flex justify-between w-full max-w-lg mx-auto bg-[#050505]">
            
            <button x-show="paso > (modoActual === 'editar' ? 2 : 1)" @click="paso--" class="w-1/3 md:w-auto bg-gray-800 text-white font-bold py-3 px-6 rounded-xl text-sm transition-colors">Atrás</button>
            <div x-show="paso === 1" class="hidden md:block"></div>
            
            <button x-show="paso === 1 && modoActual === 'crear'" @click="avanzar(2)" :disabled="!nickValidado" :class="!nickValidado ? 'opacity-50 cursor-not-allowed bg-gray-800' : 'bg-blue-600 hover:bg-blue-500'" class="w-full md:w-auto text-white font-bold py-3 px-6 rounded-xl text-sm md:ml-auto transition-colors">
                Continuar
            </button>
            
            <button x-show="paso >= 2 && paso < totalPasos" @click="avanzar(paso === 2 && tipoPerfil === 'empresa' ? 4 : paso + 1)" class="w-2/3 md:w-auto bg-blue-600 text-white font-bold py-3 px-6 rounded-xl text-sm md:ml-auto">
                Continuar
            </button>

            <button x-show="paso === totalPasos" @click="guardarPerfilCompleto" class="w-2/3 md:w-auto bg-green-600 text-white font-bold py-3 px-6 rounded-xl text-sm md:ml-auto flex items-center justify-center gap-2">
                <span x-show="!guardando">Finalizar y Guardar</span>
                <span x-show="guardando" class="animate-pulse">Guardando...</span>
            </button>
        </div>
    </div>

    <!-- 📱 BOTÓN FLOTANTE MÓVIL PARA VER PREVIEW -->
    <button @click="modalPreviewMovil = true" class="md:hidden fixed bottom-24 right-4 z-40 bg-white text-black rounded-full h-12 px-5 flex items-center justify-center font-bold text-sm shadow-[0_10px_25px_rgba(255,255,255,0.2)] gap-2">
        👀 Ver Tarjeta
    </button>

    <!-- MITAD DERECHA (PC) O MODAL FULLSCREEN (MÓVIL): Live Preview -->
    <div :class="modalPreviewMovil ? 'fixed inset-0 z-[200] flex bg-black/95 backdrop-blur-md flex-col items-center justify-center p-4' : 'hidden md:flex w-1/2 h-full bg-[#0a0a0d] items-center justify-center relative border-l border-gray-800'">
        
        <!-- Botón cerrar solo visible en Modal Móvil -->
        <button x-show="modalPreviewMovil" @click="modalPreviewMovil = false" class="md:hidden absolute top-6 right-6 w-10 h-10 bg-gray-800 rounded-full text-white font-bold z-50 flex items-center justify-center">&times;</button>
        <span x-show="modalPreviewMovil" class="md:hidden absolute top-8 left-6 text-white font-bold tracking-widest text-sm uppercase">Previsualización</span>

        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] pointer-events-none md:block hidden"></div>
        
        <div class="phone-mockup flex flex-col items-center pt-12 pb-2 relative scale-[0.95] md:scale-100">
            <div class="phone-notch"></div>
            
            <div class="w-full h-full overflow-y-auto hide-scrollbar pb-6 relative" 
                 :style="config.tema_base === 'crystal' ? 'background-image: linear-gradient(to bottom, rgba(9, 9, 11, 0.90), rgba(9, 9, 11, 0.98)), url(\'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop\'); background-size: cover;' : 'background-color: ' + config.color_fondo + ';'">
                
                <!-- Inyección de variables CSS para el Live Preview -->
                <div class="flex flex-col transition-all duration-500 min-h-[90%]" :class="`tpl-${config.tema_base || plantilla}`" :style="`--color-primario: ${config.color_primario};`">
                    
                    <!-- ESTRUCTURA FERIA -->
                    <div x-show="config.layout_estructura === 'feria'" style="display: none;" class="w-full flex flex-col items-center">
                        <div class="w-full aspect-square mb-6 overflow-hidden border-2 border-gray-800 shadow-lg" :class="config.forma_botones">
                            <img x-show="fotoPreview" :src="fotoPreview" class="w-full h-full object-cover">
                            <div x-show="!fotoPreview" class="w-full h-full bg-gray-900 flex items-center justify-center text-4xl font-black text-gray-700" x-text="nombre ? nombre.charAt(0).toUpperCase() : '?'"></div>
                        </div>
                        <div class="text-center w-full mb-4 px-4">
                            <h2 class="text-[22px] font-black tracking-tight leading-tight" x-text="nombre || 'Tu Nombre'"></h2 >
                            <h3 x-show="tipoPerfil !== 'empresa'" class="accent-text text-[10px] font-bold mt-1 uppercase tracking-widest" x-text="profesion || 'Tu Profesión'"></h3>
                        </div>
                        <div class="h-14 w-[calc(100%-2rem)] mx-4 bg-white text-black font-black text-sm flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.2)] mb-6" :class="config.forma_botones">
                            GUARDAR CONTACTO
                        </div>
                    </div>

                    <!-- ESTRUCTURA CLÁSICA -->
                    <div x-show="config.layout_estructura !== 'feria'" class="w-full">
                        <div :class="config.alineacion === 'izquierda' ? 'flex flex-row items-center gap-4 mb-4 text-left w-full px-4 pt-4' : 'flex flex-col items-center text-center w-full px-4 pt-4'">
                            <div class="avatar-ring bg-gradient-to-br from-gray-700 to-gray-900 border-4 flex items-center justify-center shadow-lg overflow-hidden flex-shrink-0" 
                                 :class="[tipoPerfil === 'empresa' ? 'rounded-2xl' : 'rounded-full', config.alineacion === 'izquierda' ? 'w-20 h-20 mb-0' : 'w-28 h-28 mb-4']">
                                <img x-show="fotoPreview" :src="fotoPreview" class="w-full h-full object-cover">
                                <span x-show="!fotoPreview" class="text-3xl text-gray-600 font-black" x-text="nombre ? nombre.charAt(0).toUpperCase() : '?'"></span>
                            </div>
                            
                            <div :class="config.alineacion === 'izquierda' ? 'flex flex-col flex-1' : 'flex flex-col items-center w-full'">
                                <h2 class="text-[18px] font-bold tracking-tight leading-tight" x-text="nombre || 'Tu Nombre'"></h2 >
                                <h3 x-show="tipoPerfil !== 'empresa'" class="accent-text text-[10px] font-bold mt-1 uppercase tracking-widest" x-text="profesion || 'Tu Profesión'"></h3>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-[11px] font-light mt-2 mb-6 px-4 leading-relaxed w-full" :class="config.alineacion === 'izquierda' ? 'text-left' : 'text-center'" x-text="bio || 'Tu biografía aparecerá aquí optimizada.'"></p>

                    <div class="w-full px-4 mb-6" x-show="galeriaPreviews.length > 0" style="display: none;">
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="(img, index) in galeriaPreviews" :key="index">
                                <div class="relative w-full aspect-square overflow-hidden border border-gray-800" :class="config.forma_botones">
                                    <img :src="img" class="w-full h-full object-cover">
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="w-full px-4 flex gap-2 mb-4">
                        <div x-show="telefono" class="h-10 bg-gray-800/50 border border-gray-700 w-1/2 flex items-center justify-center text-xs text-white font-bold gap-2 cursor-pointer hover:bg-gray-700" :class="config.forma_botones">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg> SMS
                        </div>
                        <div x-show="telefono" class="h-10 bg-[#25D366]/20 border border-[#25D366]/40 w-1/2 flex items-center justify-center text-xs text-[#4ade80] font-bold gap-2 cursor-pointer" :class="config.forma_botones">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg> WhatsApp
                        </div>
                    </div>

                    <!-- TAMAÑO DE BOTONES DE REDES REDUCIDO A h-10 -->
                    <div class="w-full px-4 grid grid-cols-2 gap-2 mb-4" x-show="redesSeleccionadas.length > 0">
                        <template x-for="red in redesSeleccionadas" :key="red.id">
                            <div class="h-10 flex items-center justify-center text-[10px] font-bold gap-2 cursor-pointer bg-gray-900 text-white border border-gray-800" :class="config.forma_botones">
                                <div class="w-4 h-4" x-html="red.svg"></div> <span x-text="red.nombre" class="uppercase tracking-wider"></span>
                            </div>
                        </template>
                    </div>

                    <div class="w-full px-4 space-y-2 mb-6">
                        <div x-show="zelle" class="w-full h-11 btn-zelle flex items-center justify-center text-xs font-bold cursor-pointer gap-2" :class="config.forma_botones">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg> Pagar con Zelle
                        </div>
                        <div x-show="cashapp" class="w-full h-11 btn-cashapp flex items-center justify-center text-xs font-bold cursor-pointer" :class="config.forma_botones">Pagar con CashApp</div>
                        <div x-show="venmo" class="w-full h-11 btn-venmo flex items-center justify-center text-xs font-bold cursor-pointer" :class="config.forma_botones">Pagar con Venmo</div>
                    </div>

                    <div x-show="config.layout_estructura !== 'feria'" class="h-11 w-[calc(100%-2rem)] mx-4 bg-white text-black font-bold text-xs flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.2)] cursor-pointer hover:bg-gray-200 mt-auto gap-2" :class="config.forma_botones">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Guardar Contacto
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CROPPER -->
    <div x-show="cropperModalAbierto" class="fixed inset-0 bg-black/95 z-[9999] flex items-center justify-center p-4 backdrop-blur-md" style="display: none;">
        <div class="bg-[#121214] border border-gray-800 rounded-3xl p-6 w-full max-w-md flex flex-col items-center shadow-2xl">
            <h3 class="text-white font-bold mb-2">Ajusta tu foto de perfil</h3>
            <p class="text-xs text-gray-400 mb-6 text-center">Usa los botones o tus dedos para acercar, alejar o mover la foto hasta que quede perfecta.</p>
            
            <div class="w-full max-w-[300px] aspect-square bg-black rounded-xl overflow-hidden mb-4 relative border border-gray-700 shadow-inner">
                <img id="imagen-cropper" :src="imgParaRecortar" class="max-w-full block">
            </div>
            
            <div class="flex gap-4 mb-6">
                <button type="button" @click="window.cropperInstancia.zoom(-0.1)" class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center text-white font-bold hover:bg-gray-700 text-xl">-</button>
                <button type="button" @click="window.cropperInstancia.zoom(0.1)" class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center text-white font-bold hover:bg-gray-700 text-xl">+</button>
            </div>

            <div class="flex gap-4 w-full">
                <button type="button" @click="cancelarRecorte" class="flex-1 bg-gray-800 text-white font-bold py-3 rounded-xl hover:bg-gray-700 transition-colors">Cancelar</button>
                <button type="button" @click="aplicarRecorte" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-500 shadow-[0_0_15px_rgba(59,130,246,0.4)]">Aplicar Foto</button>
            </div>
        </div>
    </div>

    <!-- MODAL GIGANTE DE ÉXITO -->
    <div x-show="modalExitoAbierto" class="fixed inset-0 bg-black/98 z-[9999] flex items-center justify-center p-6 backdrop-blur-xl" style="display: none;">
        <div class="w-full max-w-lg text-center flex flex-col items-center">
            <div class="w-24 h-24 bg-green-500/20 rounded-full flex items-center justify-center mb-8 border-2 border-green-500 shadow-[0_0_50px_rgba(0,214,50,0.3)]">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h1 class="text-4xl font-black text-white mb-4 tracking-tight" x-text="modoActual === 'editar' ? '¡Cambios Guardados!' : '¡Perfil Creado con Éxito!'"></h1>
            <p class="text-gray-300 text-lg mb-2" x-show="modoActual === 'crear'">Tu identidad digital ya está guardada y lista.</p>
            <p class="text-blue-400 font-bold mb-10 text-xl border border-blue-500/30 bg-blue-600/10 py-4 px-6 rounded-2xl inline-block shadow-lg" x-show="modoActual === 'crear'">
                Paso final: Para activar tu tarjeta inteligente, simplemente acercala ahora a la parte superior de tu celular.
            </p>
            <p class="text-gray-400 mb-10 text-lg" x-show="modoActual === 'editar'">Tus actualizaciones ya se reflejan en tu perfil y tarjeta vinculada.</p>
            <button @click="irAlDashboard" class="bg-white text-black font-black py-4 px-10 rounded-2xl transition-all shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:scale-105">
                Volver a Mis Perfiles
            </button>
        </div>
    </div>

    <!-- MODALES EXTRAS (Zelle y Lightbox) -->
    <div x-show="modalZelleAbierto" x-transition.opacity class="fixed inset-0 bg-black/90 z-[9999] flex flex-col justify-end backdrop-blur-sm" style="display: none;">
        <div class="bg-[#121214] w-full rounded-t-2xl p-5 border-t border-gray-800 relative" @click.away="modalZelleAbierto = false">
            <button @click="modalZelleAbierto = false" class="absolute top-4 right-4 text-gray-500 hover:text-white text-2xl">&times;</button>
            <div class="flex items-center justify-center gap-2 text-white font-bold text-sm mb-4">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg> Zelle Transfer
            </div>
            <div class="bg-[#25d366]/10 border border-[#25d366]/40 rounded-xl p-3 mb-4 text-center">
                <span class="text-[#f59e0b] font-black text-sm block mb-1" x-text="zelle"></span>
                <span class="text-[#25d366] text-[10px] font-bold">✓ Copiado al portapapeles</span>
            </div>
        </div>
    </div>

    <div x-show="lightboxAbierto" x-transition.opacity class="fixed inset-0 bg-black/95 z-[9999] flex items-center justify-center p-4 backdrop-blur-md" style="display: none;" @click="lightboxAbierto = false">
        <button class="absolute top-4 right-4 text-white text-4xl font-light" @click="lightboxAbierto = false">&times;</button>
        <img :src="imgActivaLightbox" class="w-full rounded-xl object-contain max-h-full">
    </div>

    <script>
        function perfilDigital() {
            return {
                id_perfil_editar: null, 
                modoActual: 'crear', 
                paso: 1, totalPasos: 9,
                alineacion: 'centro', plantilla: 'crystal', tipoPerfil: 'corporativo',
                
                nickname: '', nickValidado: false, nickError: '', 
                nombre: '', profesion: '', bio: '',
                telefono: '', correo: '', direccion: '', 
                zelle: '', cashapp: '', venmo: '',
                
                fotoPreview: null, galeriaPreviews: [], toastError: '',
                validandoNick: false, guardando: false, generandoIA: false, 
                cropperModalAbierto: false, modalExitoAbierto: false, imgParaRecortar: '',
                modalZelleAbierto: false, lightboxAbierto: false, imgActivaLightbox: '',
                
                modalPreviewMovil: false, tarjetaVinculada: false, 
                
                nombrePerfil: 'Mi Tarjeta Principal',
                config: { tema_base: 'crystal', color_fondo: 'transparent', color_primario: '#3b82f6', alineacion: 'centro', forma_botones: 'rounded-2xl', layout_estructura: 'clasica' },
                presets: { luxury: { tema_base: 'luxury', color_fondo: '#050505', color_primario: '#D4AF37', alineacion: 'centro', forma_botones: 'rounded-none', layout_estructura: 'clasica' }, sport: { tema_base: 'sport', color_fondo: '#000000', color_primario: '#ccff00', alineacion: 'izquierda', forma_botones: '-skew-x-12', layout_estructura: 'clasica' }, corporativo: { tema_base: 'corporativo', color_fondo: '#f8fafc', color_primario: '#0f172a', alineacion: 'izquierda', forma_botones: 'rounded-md', layout_estructura: 'clasica' }, crystal: { tema_base: 'crystal', color_fondo: 'transparent', color_primario: '#3b82f6', alineacion: 'centro', forma_botones: 'rounded-2xl', layout_estructura: 'clasica' }, minimalista: { tema_base: 'minimalista', color_fondo: '#ffffff', color_primario: '#000000', alineacion: 'centro', forma_botones: 'rounded-full', layout_estructura: 'clasica' }, feria: { tema_base: 'matte', color_fondo: '#09090b', color_primario: '#ff3b30', alineacion: 'centro', forma_botones: 'rounded-xl', layout_estructura: 'feria' } },

                aplicarPreset(nombre) { 
                    this.config = { ...this.presets[nombre] }; 
                    this.plantilla = this.config.tema_base; 
                    this.alineacion = this.config.alineacion; 
                },
                
                opcionesRedes: [
                    { id: 'ig', nombre: 'Instagram', claseCSS: 'btn-ig', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path></svg>' },
                    { id: 'fb', nombre: 'Facebook', claseCSS: 'btn-fb', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>' },
                    { id: 'tk', nombre: 'TikTok', claseCSS: 'btn-tk', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>' },
                    { id: 'in', nombre: 'LinkedIn', claseCSS: 'btn-in', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>' },
                    { id: 'x', nombre: 'X (Twitter)', claseCSS: 'btn-tk', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733 -16z"></path><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"></path></svg>' },
                    { id: 'yt', nombre: 'YouTube', claseCSS: 'btn-ig', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33 2.78 2.78 0 0 0 1.94 2c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.33 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>' }
                ],
                redesSeleccionadas: [],

                titulos: {
                    1: 'Tu Nombre Digital', 2: 'Identidad Visual', 3: 'Tu Especialidad',
                    4: 'Biografía', 5: 'Portafolio Visual', 6: 'Información y Redes',
                    7: 'Módulo de Pagos', 8: 'Diseño Personalizado', 9: 'Guardar Perfil'
                },
                subtitulos: {
                    1: 'Crea tu usuario único.', 2: 'Define tu estilo y sube tu foto de perfil.', 3: 'Añade tu cargo principal.',
                    4: 'Describe brevemente lo que haces.', 5: 'Sube fotos para crear una cuadrícula visual.',
                    6: 'Elige cómo prefieres que te contacten.', 7: 'Agiliza tus cobros rápidamente.',
                    8: 'Mezcla la distribución y los colores a tu gusto.', 9: 'Bautiza tu configuración.'
                },

                get progreso() { return Math.round((this.paso / this.totalPasos) * 100); },

                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    this.modoActual = 'crear';
                    
                    @if(isset($perfil) && $perfil)
                        this.id_perfil_editar = {{ $perfil->id }};
                        this.nickname = @json($perfil->nickname) || '';
                        this.nombre = @json($perfil->nombre) || '';
                        this.profesion = @json($perfil->profesion) || '';
                        this.bio = @json($perfil->bio) || '';
                        this.telefono = @json($perfil->telefono) || '';
                        this.correo = @json($perfil->correo_contacto) || '';
                        this.direccion = @json($perfil->direccion) || '';
                        this.zelle = @json($perfil->zelle) || '';
                        this.cashapp = @json($perfil->cashapp) || '';
                        this.venmo = @json($perfil->venmo) || '';
                        this.tipoPerfil = @json($perfil->tipo_perfil) || 'corporativo';
                        this.nombrePerfil = @json($perfil->nombre_perfil) || 'Mi Tarjeta Principal';
                        this.fotoPreview = @json($perfil->foto_perfil);
                        
                        let r = @json($perfil->redes_sociales);
                        this.redesSeleccionadas = typeof r === 'string' ? JSON.parse(r||'[]') : (r||[]);

                        let g = @json($perfil->galeria);
                        this.galeriaPreviews = typeof g === 'string' ? JSON.parse(g||'[]') : (g||[]);

                        let c = @json($perfil->design_config);
                        let pConf = typeof c === 'string' ? JSON.parse(c||'null') : c;
                        if(pConf) {
                            this.config = pConf;
                            this.plantilla = pConf.tema_base || 'crystal';
                            this.alineacion = pConf.alineacion || 'centro';
                        }
                        this.modoActual = 'editar';
                        this.tarjetaVinculada = true;
                    @endif

                    if (urlParams.has('step')) {
                        this.paso = parseInt(urlParams.get('step'));
                        this.modoActual = 'editar';
                    } else if (urlParams.has('id') || urlParams.has('editar') || urlParams.has('modo')) {
                        this.modoActual = 'editar';
                    }

                    // Magia: Si es edición, el usuario no pasa por el Paso 1
                    if (this.modoActual === 'editar' && !urlParams.has('step')) {
                        this.paso = 2;
                    }
                },

                async validarNickname() {
                    if (this.nickname.trim() === '') { 
                        this.nickError = "Debes ingresar un usuario para validar."; 
                        return; 
                    }
                    this.validandoNick = true;
                    this.nickError = '';
                    try {
                        let res = await fetch('/perfil/validar-nickname?nickname=' + this.nickname.toLowerCase().replace(/\s+/g, ''));
                        let data = await res.json();
                        if (data.disponible) {
                            this.nickValidado = true; // Desbloquea botón Continuar
                        } else {
                            this.nickValidado = false;
                            this.nickError = "Lo sentimos, el usuario '" + this.nickname + "' ya está en uso.";
                        }
                    } catch (e) {
                        this.nickError = "Error al validar el enlace.";
                    }
                    this.validandoNick = false;
                },

                async mejorarConIA() {
                    if (!this.profesion && !this.bio) { 
                        this.toastError = "Escribe al menos tu profesión en el paso anterior para que la IA sepa qué redactar."; 
                        return; 
                    }
                    this.generandoIA = true;
                    try {
                        let response = await fetch('/api/ia/generar-bio', { 
                            method: 'POST', 
                            headers: { 
                                'Content-Type': 'application/json', 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                            }, 
                            body: JSON.stringify({ profesion: this.profesion, bio_actual: this.bio }) 
                        });
                        let data = await response.json();
                        if (data.status === 'success') { 
                            this.bio = data.bio; 
                        } else { 
                            this.toastError = data.message || "La IA no pudo responder, intenta de nuevo."; 
                        }
                    } catch (e) { 
                        this.toastError = "Error conectando con el motor de IA."; 
                    }
                    this.generandoIA = false;
                },

                avanzar(siguientePaso) {
                    this.toastError = '';
                    this.paso = siguientePaso;
                },

                agregarRed(red) { 
                    if (this.redesSeleccionadas.length < 4) {
                        this.redesSeleccionadas.push({ ...red, usuario: '' }); 
                    }
                },
                
                quitarRed(index) { 
                    this.redesSeleccionadas.splice(index, 1); 
                },
                
                abrirLightboxMockup(imgSrc) { 
                    this.imgActivaLightbox = imgSrc; 
                    this.lightboxAbierto = true; 
                },

                abrirRecorte(e) {
                    let file = e.target.files[0];
                    if(!file) return;
                    let reader = new FileReader();
                    reader.onload = (event) => { 
                        this.imgParaRecortar = event.target.result; 
                        this.cropperModalAbierto = true;
                        
                        setTimeout(() => {
                            let imageElement = document.getElementById('imagen-cropper');
                            if(window.cropperInstancia) window.cropperInstancia.destroy();
                            
                            window.cropperInstancia = new Cropper(imageElement, {
                                aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 1, cropBoxMovable: false, cropBoxResizable: false, zoomable: true, guides: false, background: false
                            });
                        }, 100);
                    };
                    reader.readAsDataURL(file);
                    e.target.value = '';
                },

                cancelarRecorte() {
                    this.cropperModalAbierto = false;
                    if(window.cropperInstancia) { window.cropperInstancia.destroy(); window.cropperInstancia = null; }
                },

                aplicarRecorte() {
                    if(window.cropperInstancia) {
                        this.fotoPreview = window.cropperInstancia.getCroppedCanvas({ width: 500, height: 500 }).toDataURL('image/jpeg', 0.85);
                        this.cancelarRecorte();
                    }
                },

                procesarImagenesGaleria(event) {
                    const archivos = event.target.files;
                    if (!archivos) return;
                    let disp = 4 - this.galeriaPreviews.length;
                    for (let i = 0; i < Math.min(archivos.length, disp); i++) {
                        let reader = new FileReader();
                        reader.onload = (e) => { this.galeriaPreviews.push(e.target.result); };
                        reader.readAsDataURL(archivos[i]);
                    }
                },
                
                eliminarImagenGaleria(index) { 
                    this.galeriaPreviews.splice(index, 1); 
                },

                async guardarPerfilCompleto() {
                    if(this.modoActual === 'crear' && (!this.nombre || !this.nickname)) {
                        this.toastError = "Por favor, vuelve al inicio y llena tu Nombre y Usuario.";
                        return;
                    }
                    if (this.modoActual === 'editar' && !this.nombre) {
                        this.toastError = "El nombre es obligatorio.";
                        return;
                    }

                    this.guardando = true;
                    // La Forja: la unidad DT-9 ensambla el perfil mientras el servidor lo guarda
                    const forja = window.Forja;
                    forja?.iniciar({ inicial: this.nombre || this.nickname, color: this.config.color_primario, modo: this.modoActual });

                    let formData = new FormData();
                    formData.append('perfil_id', this.id_perfil_editar || 'null');
                    formData.append('nickname', this.nickname.toLowerCase().replace(/\s+/g, ''));
                    formData.append('nombre', this.nombre);
                    formData.append('tipo_perfil', this.tipoPerfil);
                    formData.append('plantilla', this.plantilla); 
                    formData.append('alineacion', this.alineacion); 
                    formData.append('profesion', this.profesion);
                    formData.append('bio', this.bio);
                    formData.append('telefono', this.telefono);
                    formData.append('correo_contacto', this.correo);
                    formData.append('direccion', this.direccion);
                    formData.append('zelle', this.zelle);
                    formData.append('cashapp', this.cashapp);
                    formData.append('venmo', this.venmo);
                    formData.append('foto_perfil', this.fotoPreview || '');
                    formData.append('redes_sociales', JSON.stringify(this.redesSeleccionadas));
                    formData.append('galeria', JSON.stringify(this.galeriaPreviews));
                    
                    formData.append('nombre_perfil', this.nombrePerfil);
                    formData.append('design_config', JSON.stringify(this.config));

                    try {
                        let respuesta = await fetch('/perfil/guardar', {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        
                        let resultado = await respuesta.json();
                        
                        if(resultado.status === 'success') {
                            forja ? forja.terminar({ ok: true, nickname: this.nickname.toLowerCase().replace(/\s+/g, '') }) : (this.modalExitoAbierto = true);
                        } else {
                            this.toastError = resultado.message || "Error al procesar los datos.";
                            forja?.terminar({ ok: false, mensaje: this.toastError });
                        }
                    } catch (error) {
                        this.toastError = 'Error de conexión con el servidor.';
                        forja?.terminar({ ok: false, mensaje: this.toastError });
                    }
                    this.guardando = false;
                },

                irAlDashboard() { 
                    window.location.href = '/dashboard/perfiles'; 
                }
            }
        }
    </script>

    @include('partials.forja')
</body>
</html>