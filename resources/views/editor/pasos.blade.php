<div class="w-full md:w-1/2 h-full flex flex-col relative z-10">
    
    <!-- BARRA DE PROGRESO -->
    <div class="w-full px-8 pt-8 pb-4">
        <div class="flex justify-between items-end mb-2">
            <h2 class="text-xl md:text-2xl font-black text-white" x-text="titulos[paso]"></h2>
            <span class="text-xl md:text-3xl font-black text-blue-500" x-text="`${progreso}%`"></span>
        </div>
        <p class="text-xs text-gray-400 mb-4" x-text="subtitulos[paso]"></p>
        <div class="w-full h-1.5 bg-gray-800 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-blue-600 to-purple-500 transition-all duration-500" :style="`width: ${progreso}%`"></div>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto hide-scrollbar px-6 md:px-8 pb-8 flex flex-col mobile-content-area">
        <div class="glass-panel p-6 md:p-8 rounded-3xl w-full max-w-lg mx-auto transition-all duration-500 relative mt-2 md:mt-4">
            
            <!-- PASO 1: Nickname (Alertas en línea ocultas de arriba) -->
            <div x-show="paso === 1 && modoActual === 'crear'" x-transition.opacity.duration.300ms>
                <label class="block text-sm font-semibold mb-2 text-gray-300">Crea tu usuario único</label>
                <div class="flex items-center gap-3 mb-2">
                    <input type="text" x-model="nickname" @input="nickValidado = false; nickError = ''" placeholder="ej. carlos" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 outline-none lowercase focus:border-blue-500 transition-colors">
                    
                    <button @click="validarNickname" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-xl transition-colors whitespace-nowrap flex items-center gap-2">
                        <span x-show="!validandoNick">Validar</span>
                        <span x-show="validandoNick" class="animate-pulse">...</span>
                    </button>
                </div>
                <!-- Mensajes Inline debajo del input -->
                <p x-show="nickValidado" class="text-green-400 text-xs font-bold ml-2 mb-4">✓ Usuario disponible</p>
                <p x-show="nickError !== ''" class="text-red-500 text-xs font-bold ml-2 mb-4" x-text="nickError"></p>
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

            <!-- PASO 4: Biografía + IA -->
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
                    <div class="flex items-center bg-black border border-gray-800 rounded-xl overflow-hidden focus-within:border-blue-500"><span class="pl-4 text-gray-500">📞</span><input type="tel" x-model="telefono" placeholder="Teléfono" class="w-full bg-transparent text-white px-4 py-3 outline-none"></div>
                    <div class="flex items-center bg-black border border-gray-800 rounded-xl overflow-hidden focus-within:border-blue-500"><span class="pl-4 text-gray-500">✉️</span><input type="email" x-model="correo" placeholder="Correo Electrónico" class="w-full bg-transparent text-white px-4 py-3 outline-none"></div>
                    <div class="flex items-center bg-black border border-gray-800 rounded-xl overflow-hidden focus-within:border-blue-500"><span class="pl-4 text-gray-500">📍</span><input type="text" x-model="direccion" placeholder="Dirección (Opcional)" class="w-full bg-transparent text-white px-4 py-3 outline-none"></div>
                </div>

                <label class="block text-sm font-semibold mb-2 text-gray-300 flex justify-between">Redes Sociales <span class="text-xs text-blue-400"><span x-text="redesSeleccionadas.length"></span>/4</span></label>
                
                <div class="grid grid-cols-4 gap-2 mb-4" :class="redesSeleccionadas.length >= 4 ? 'opacity-50 pointer-events-none' : ''">
                    <template x-for="red in opcionesRedes" :key="red.id">
                        <button @click="agregarRed(red)" x-show="!redesSeleccionadas.find(r => r.id === red.id)" class="bg-gray-900 border border-gray-700 hover:border-blue-500 rounded-lg py-2 text-xs font-bold transition-all flex flex-col items-center gap-1 text-gray-400 hover:text-white">
                            <div x-html="red.svg" class="w-5 h-5"></div>
                        </button>
                    </template>
                </div>
                
                <p class="text-[10px] text-gray-500 mb-3" x-show="redesSeleccionadas.length > 0">⚠️ Ingresa solo tu nombre de usuario (ej. carlos_123).</p>
                <div class="space-y-3 mb-6">
                    <template x-for="(red, index) in redesSeleccionadas" :key="index">
                        <div class="flex items-center bg-black border border-gray-800 rounded-xl overflow-hidden focus-within:border-blue-500">
                            <div class="pl-4 text-gray-400" x-html="red.svg"></div>
                            <input type="text" x-model="red.usuario" :placeholder="'Usuario de ' + red.nombre" class="w-full bg-transparent text-white px-4 py-3 outline-none text-sm">
                            <button @click="quitarRed(index)" class="px-4 text-red-500 font-bold">&times;</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- PASO 7: Billeteras -->
            <div x-show="paso === 7" x-transition.opacity.duration.300ms style="display: none;">
                <label class="block text-sm font-semibold mb-4 text-gray-300">Billeteras Digitales (Opcional)</label>
                <div class="space-y-4 mb-6">
                    <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><div class="w-6 h-6 rounded bg-[#741b8c] flex items-center justify-center text-white font-bold text-xs">Z</div></div><input type="text" x-model="zelle" placeholder="Correo/Teléfono Zelle" class="w-full bg-black border border-gray-800 rounded-xl pl-12 pr-4 py-3 text-white outline-none focus:border-[#a855f7]"></div>
                    <div class="relative"><div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><div class="w-6 h-6 rounded bg-[#00D632] flex items-center justify-center text-black font-bold text-xs">$</div></div><input type="text" x-model="cashapp" placeholder="$CashApp" class="w-full bg-black border border-gray-800 rounded-xl pl-12 pr-4 py-3 text-white outline-none focus:border-[#00D632]"></div>
                </div>
            </div>

            <!-- PASO 8: DISEÑO -->
            <div x-show="paso === 8" x-transition.opacity.duration.300ms style="display: none;">
                <label class="block text-xs font-bold mb-3 text-gray-400 uppercase tracking-widest">1. Estructura y Plantillas</label>
                <div class="flex overflow-x-auto gap-3 pb-4 hide-scrollbar mb-4">
                    <button @click="aplicarPreset('crystal')" :class="config.tema_base === 'crystal' && config.layout_estructura !== 'feria' ? 'ring-2 ring-blue-500 scale-105' : 'opacity-70'" class="w-24 h-32 flex-shrink-0 bg-blue-900/40 border border-white/20 rounded-xl flex flex-col items-center justify-center backdrop-blur-md transition-all"><span class="text-white font-sans font-bold text-xs">CRYSTAL</span></button>
                    <button @click="aplicarPreset('feria')" :class="config.layout_estructura === 'feria' ? 'ring-2 ring-red-500 scale-105' : 'opacity-70'" class="w-24 h-32 flex-shrink-0 bg-black border border-red-500 rounded-xl flex flex-col items-center justify-center transition-all"><span class="text-red-500 font-sans font-black text-xs uppercase text-center leading-tight">MODO<br>FERIA</span></button>
                    <button @click="aplicarPreset('luxury')" :class="config.tema_base === 'luxury' && config.layout_estructura !== 'feria' ? 'ring-2 ring-yellow-500 scale-105' : 'opacity-70'" class="w-24 h-32 flex-shrink-0 bg-[#050505] border-2 border-[#D4AF37] rounded-xl flex flex-col items-center justify-center transition-all"><span class="text-[#D4AF37] font-serif font-bold text-xs">LUXURY</span></button>
                </div>
                <hr class="border-gray-800 mb-6">
                <label class="block text-xs font-bold mb-3 text-gray-400 uppercase tracking-widest">2. Ajuste Fino</label>
                <div class="space-y-4">
                    <div class="flex items-center justify-between bg-black p-3 rounded-xl border border-gray-800">
                        <span class="text-sm text-gray-300">Color Primario</span><input type="color" x-model="config.color_primario" class="w-8 h-8 rounded cursor-pointer bg-transparent border-none">
                    </div>
                    <div class="flex gap-2 bg-black p-1 rounded-xl border border-gray-800">
                        <button @click="config.forma_botones = 'rounded-none'" :class="config.forma_botones === 'rounded-none' ? 'bg-gray-800 text-white' : 'text-gray-500'" class="flex-1 py-2 rounded-lg text-xs">Cuadrado</button>
                        <button @click="config.forma_botones = 'rounded-xl'" :class="config.forma_botones === 'rounded-xl' ? 'bg-gray-800 text-white' : 'text-gray-500'" class="flex-1 py-2 rounded-lg text-xs">Suave</button>
                        <button @click="config.forma_botones = 'rounded-full'" :class="config.forma_botones === 'rounded-full' ? 'bg-gray-800 text-white' : 'text-gray-500'" class="flex-1 py-2 rounded-lg text-xs">Píldora</button>
                    </div>
                </div>
            </div>

            <!-- PASO 9: BAUTIZAR -->
            <div x-show="paso === 9" x-transition.opacity.duration.300ms style="display: none;">
                <label class="block text-sm font-semibold mb-2 text-gray-300">Bautiza esta configuración</label>
                <p class="text-xs text-gray-500 mb-6">Identifica este diseño en tu panel (ej. "Perfil Trabajo").</p>
                <input type="text" x-model="nombrePerfil" placeholder="Mi Tarjeta Principal" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">
            </div>
        </div>
    </div>

    <!-- BOTONERA INFERIOR -->
    <div class="mobile-fixed-bottom md:static md:px-8 md:pb-8 md:pt-4 md:border-none flex justify-between bg-[#050505]">
        <button x-show="paso > (modoActual === 'editar' ? 2 : 1)" @click="paso--" class="w-1/3 md:w-auto bg-gray-800 text-white font-bold py-3 px-6 rounded-xl text-sm transition-colors">Atrás</button>
        <div x-show="paso === 1" class="hidden md:block"></div>
        <button x-show="paso === 1 && modoActual === 'crear'" @click="avanzar(2)" :disabled="!nickValidado" :class="!nickValidado ? 'opacity-50 cursor-not-allowed bg-gray-800' : 'bg-blue-600 hover:bg-blue-500'" class="w-full md:w-auto text-white font-bold py-3 px-6 rounded-xl text-sm md:ml-auto">Continuar</button>
        <button x-show="paso >= 2 && paso < totalPasos" @click="avanzar(paso === 2 && tipoPerfil === 'empresa' ? 4 : paso + 1)" class="w-2/3 md:w-auto bg-blue-600 text-white font-bold py-3 px-6 rounded-xl text-sm md:ml-auto">Continuar</button>
        <button x-show="paso === totalPasos" @click="guardarPerfilCompleto" class="w-2/3 md:w-auto bg-green-600 text-white font-bold py-3 px-6 rounded-xl text-sm md:ml-auto flex items-center justify-center gap-2">
            <span x-show="!guardando">Finalizar y Guardar</span><span x-show="guardando" class="animate-pulse">Guardando...</span>
        </button>
    </div>
</div>