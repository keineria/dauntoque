<button @click="modalPreviewMovil = true" class="md:hidden fixed bottom-24 right-4 z-40 bg-white text-black rounded-full h-12 px-5 flex items-center justify-center font-bold text-sm shadow-[0_10px_25px_rgba(255,255,255,0.2)] gap-2">👀 Ver Tarjeta</button>

<div :class="modalPreviewMovil ? 'fixed inset-0 z-[200] flex bg-black/95 backdrop-blur-md flex-col items-center justify-center p-4' : 'hidden md:flex w-1/2 h-full bg-[#0a0a0d] items-center justify-center relative border-l border-gray-800'">
    <button x-show="modalPreviewMovil" @click="modalPreviewMovil = false" class="md:hidden absolute top-6 right-6 w-10 h-10 bg-gray-800 rounded-full text-white font-bold z-50 flex items-center justify-center">&times;</button>
    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] pointer-events-none md:block hidden"></div>
    
    <div class="phone-mockup flex flex-col items-center pt-12 pb-2 relative scale-[0.95] md:scale-100">
        <div class="phone-notch"></div>
        <div class="w-full h-full overflow-y-auto hide-scrollbar pb-6 relative" :style="config.tema_base === 'crystal' ? 'background-image: linear-gradient(to bottom, rgba(9, 9, 11, 0.90), rgba(9, 9, 11, 0.98)), url(\'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop\'); background-size: cover;' : 'background-color: ' + config.color_fondo + ';'">
            
            <div class="flex flex-col transition-all duration-500 min-h-[90%]" :class="`tpl-${config.tema_base || plantilla}`" :style="`--color-primario: ${config.color_primario};`">
                
                <div x-show="config.layout_estructura === 'feria'" style="display: none;" class="w-full flex flex-col items-center">
                    <div class="w-full aspect-square mb-6 overflow-hidden border-2 border-gray-800 shadow-lg" :class="config.forma_botones">
                        <img x-show="fotoPreview" :src="fotoPreview" class="w-full h-full object-cover">
                        <div x-show="!fotoPreview" class="w-full h-full bg-gray-900 flex items-center justify-center text-4xl font-black text-gray-700" x-text="nombre ? nombre.charAt(0).toUpperCase() : '?'"></div>
                    </div>
                    <div class="text-center w-full mb-4 px-4">
                        <h2 class="text-[22px] font-black tracking-tight leading-tight" x-text="nombre || 'Tu Nombre'"></h2 >
                        <h3 x-show="tipoPerfil !== 'empresa'" class="accent-text text-[10px] font-bold mt-1 uppercase tracking-widest" x-text="profesion || 'Tu Profesión'"></h3>
                    </div>
                    <div class="h-14 w-[calc(100%-2rem)] mx-4 bg-white text-black font-black text-sm flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.2)] mb-6" :class="config.forma_botones">GUARDAR CONTACTO</div>
                </div>

                <div x-show="config.layout_estructura !== 'feria'" class="w-full">
                    <div :class="config.alineacion === 'izquierda' ? 'flex flex-row items-center gap-4 mb-4 text-left w-full px-4 pt-4' : 'flex flex-col items-center text-center w-full px-4 pt-4'">
                        <div class="avatar-ring bg-gradient-to-br from-gray-700 to-gray-900 border-4 flex items-center justify-center shadow-lg overflow-hidden flex-shrink-0" :class="[tipoPerfil === 'empresa' ? 'rounded-2xl' : 'rounded-full', config.alineacion === 'izquierda' ? 'w-20 h-20 mb-0' : 'w-28 h-28 mb-4']">
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
                    <div class="grid grid-cols-2 gap-2"><template x-for="(img, index) in galeriaPreviews" :key="index"><div class="relative w-full aspect-square overflow-hidden border border-gray-800" :class="config.forma_botones"><img :src="img" class="w-full h-full object-cover"></div></template></div>
                </div>

                <div class="w-full px-4 flex gap-2 mb-4">
                    <div x-show="telefono" class="h-10 bg-gray-800/50 border border-gray-700 w-1/2 flex items-center justify-center text-xs text-white font-bold gap-2 cursor-pointer hover:bg-gray-700" :class="config.forma_botones"> SMS</div>
                    <div x-show="telefono" class="h-10 bg-[#25D366]/20 border border-[#25D366]/40 w-1/2 flex items-center justify-center text-xs text-[#4ade80] font-bold gap-2 cursor-pointer" :class="config.forma_botones">WhatsApp</div>
                </div>

                <div class="w-full px-4 grid grid-cols-2 gap-2 mb-4" x-show="redesSeleccionadas.length > 0">
                    <template x-for="red in redesSeleccionadas" :key="red.id">
                        <div class="h-10 flex items-center justify-center text-[10px] font-bold gap-2 cursor-pointer bg-gray-900 text-white border border-gray-800" :class="config.forma_botones">
                            <div class="w-4 h-4" x-html="red.svg"></div> <span x-text="red.nombre" class="uppercase tracking-wider"></span>
                        </div>
                    </template>
                </div>

                <div class="w-full px-4 space-y-2 mb-6">
                    <div x-show="zelle" class="w-full h-11 btn-zelle flex items-center justify-center text-xs font-bold cursor-pointer gap-2" :class="config.forma_botones">Pagar con Zelle</div>
                    <div x-show="cashapp" class="w-full h-11 btn-cashapp flex items-center justify-center text-xs font-bold cursor-pointer" :class="config.forma_botones">Pagar con CashApp</div>
                </div>

                <div x-show="config.layout_estructura !== 'feria'" class="h-11 w-[calc(100%-2rem)] mx-4 bg-white text-black font-bold text-xs flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.2)] cursor-pointer hover:bg-gray-200 mt-auto gap-2" :class="config.forma_botones">Guardar Contacto</div>
            </div>
        </div>
    </div>
</div>