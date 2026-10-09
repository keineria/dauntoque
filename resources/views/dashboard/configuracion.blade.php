@extends('layouts.app')

@section('contenido')
    <!-- INYECCIÓN GLOBAL: Evita que el compilador de Laravel rompa las variables de JavaScript -->
    <script>
        window.perfilCargado = {!! $datosJsonSeguros !!};
    </script>

    <div x-data="gestorConfiguracion()" class="pb-10">
        
        <div class="sticky top-0 z-40 bg-[#050505]/90 backdrop-blur-xl pt-2 pb-4 mb-8 border-b border-gray-800 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight mb-1">Configuración del Perfil</h2>
                <p class="text-xs text-gray-400">Personaliza y actualiza tu tarjeta digital en tiempo real.</p>
            </div>
            <button @click="guardarPerfil" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-all shadow-[0_0_20px_rgba(59,130,246,0.3)] flex items-center gap-2">
                <svg x-show="!guardando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span x-show="!guardando">Guardar Cambios</span>
                <span x-show="guardando" class="animate-pulse">Actualizando...</span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-8">
                <!-- Identidad Visual -->
                <div class="metric-card p-8 rounded-3xl">
                    <h3 class="text-white font-bold mb-8 border-b border-gray-700 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> 
                        Identidad Visual
                    </h3>
                    
                    <div class="relative mb-14">
                        <div class="w-full h-32 rounded-xl border-2 border-dashed border-gray-700 overflow-hidden relative bg-black flex items-center justify-center group hover:border-blue-500 transition-colors cursor-pointer">
                            <input type="file" accept="image/*" @change="cargarPortada" class="absolute inset-0 opacity-0 cursor-pointer z-20">
                            <img x-show="portadaPreview" :src="portadaPreview" class="absolute inset-0 w-full h-full object-cover opacity-60">
                            <div class="text-center relative z-10" x-show="!portadaPreview">
                                <svg class="w-6 h-6 text-gray-400 mx-auto mb-1 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                <span class="text-[10px] text-gray-400 font-bold uppercase group-hover:text-blue-400">Cambiar Portada</span>
                            </div>
                        </div>

                        <!-- Avatar con Recorte Cropper -->
                        <div class="absolute -bottom-10 left-6 flex items-end gap-3 z-30">
                            <div class="relative w-24 h-24 rounded-full border-4 border-[#121214] bg-gray-900 overflow-hidden shadow-xl flex items-center justify-center group">
                                <img x-show="fotoPreview" :src="fotoPreview" class="absolute inset-0 w-full h-full object-cover">
                                <div x-show="!fotoPreview" class="text-center pointer-events-none">
                                    <span class="text-3xl font-black text-gray-600" x-text="datos.nombre ? datos.nombre.charAt(0).toUpperCase() : '?'"></span>
                                </div>
                            </div>
                            
                            <label class="bg-gray-800 hover:bg-gray-700 text-white text-[10px] font-bold py-2 px-3 rounded-lg cursor-pointer transition-colors border border-gray-700 shadow-lg mb-2 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                                Cambiar Foto
                                <input type="file" accept="image/*" @change="abrirRecorte" class="hidden">
                            </label>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2 block">Nombre Público</label>
                                <input type="text" x-model="datos.nombre" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white text-sm outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2 block">Correo Empresarial</label>
                                <input type="email" x-model="datos.correo_contacto" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white text-sm outline-none focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2 block">Título o Cargo</label>
                            <input type="text" x-model="datos.profesion" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white text-sm outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2 block">Biografía Profesional</label>
                            <textarea x-model="datos.bio" rows="4" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white text-sm outline-none focus:border-blue-500 resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Portafolio Visual -->
                <div class="metric-card p-8 rounded-3xl">
                    <h3 class="text-white font-bold mb-6 border-b border-gray-700 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg> 
                        Portafolio Visual
                    </h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2 block">Título de la Sección</label>
                            <select x-model="datos.titulo_galeria" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white text-sm outline-none focus:border-purple-500 appearance-none">
                                <option value="Servicios">Servicios</option>
                                <option value="Productos">Productos</option>
                                <option value="Proyectos">Proyectos</option>
                                <option value="Hobbies">Hobbies / Gustos</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2 flex justify-between">
                                Imágenes Cargadas <span class="text-purple-400" x-text="`${galeria.length} / 6`"></span>
                            </label>
                            
                            <div class="grid grid-cols-3 gap-3 mb-3" x-show="galeria.length > 0">
                                <template x-for="(img, index) in galeria" :key="index">
                                    <div class="relative aspect-square rounded-lg border border-gray-700 overflow-hidden group bg-black">
                                        <img :src="img" class="w-full h-full object-cover">
                                        <button @click="eliminarFoto(index)" class="absolute top-1 right-1 bg-red-600/90 text-white rounded-md w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 text-xs">&times;</button>
                                    </div>
                                </template>
                            </div>
                            <div x-show="galeria.length < 6" class="w-full h-16 rounded-xl border-2 border-dashed border-gray-700 relative bg-black flex items-center justify-center group hover:border-purple-500 cursor-pointer">
                                <input type="file" multiple accept="image/*" @change="cargarGaleria" class="absolute inset-0 opacity-0 cursor-pointer z-20">
                                <span class="text-xs text-gray-400 font-bold group-hover:text-purple-400">+ Añadir Fotos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <!-- Contacto y Ubicación -->
                <div class="metric-card p-8 rounded-3xl">
                    <h3 class="text-white font-bold mb-6 border-b border-gray-700 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg> Contacto
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center bg-black border border-gray-800 rounded-lg overflow-hidden focus-within:border-green-500">
                            <span class="pl-4 text-green-500">📞</span>
                            <input type="text" x-model="datos.telefono" class="w-full bg-transparent text-white px-3 py-3 outline-none text-sm">
                        </div>
                        <div class="flex items-center bg-black border border-gray-800 rounded-lg overflow-hidden focus-within:border-green-500">
                            <span class="pl-4 text-gray-500">📍</span>
                            <input type="text" x-model="datos.direccion" class="w-full bg-transparent text-white px-3 py-3 outline-none text-sm">
                        </div>
                    </div>
                </div>

                <!-- Redes Sociales -->
                <div class="metric-card p-8 rounded-3xl">
                    <h3 class="text-white font-bold mb-6 border-b border-gray-700 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg> 
                        Redes Sociales <span class="text-[10px] text-gray-500 ml-auto" x-text="`${redes.length}/4`"></span>
                    </h3>
                    <div class="space-y-3 mb-4">
                        <template x-for="(red, index) in redes" :key="index">
                            <div class="flex items-center bg-black border border-gray-800 rounded-lg overflow-hidden focus-within:border-pink-500">
                                <select x-model="red.plataforma" class="bg-gray-900 text-white text-xs border-r border-gray-800 py-3 px-3 outline-none">
                                    <option value="Instagram">Instagram</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="TikTok">TikTok</option>
                                    <option value="LinkedIn">LinkedIn</option>
                                    <option value="X (Twitter)">X (Twitter)</option>
                                    <option value="YouTube">YouTube</option>
                                </select>
                                <input type="text" x-model="red.usuario" placeholder="@usuario o https://" class="w-full bg-transparent text-white px-3 py-3 outline-none text-sm">
                                <button @click="quitarRed(index)" class="px-4 text-red-500 font-bold text-lg">&times;</button>
                            </div>
                        </template>
                    </div>
                    <button x-show="redes.length < 4" @click="agregarRed" class="w-full border border-dashed border-gray-700 text-gray-400 hover:text-pink-400 text-xs font-bold py-3 rounded-lg">
                        + Añadir Red Social
                    </button>
                </div>

                <!-- Pagos -->
                <div class="metric-card p-8 rounded-3xl">
                    <h3 class="text-white font-bold mb-6 border-b border-gray-700 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#a855f7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Pagos Rápidos
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center bg-black border border-gray-800 rounded-lg overflow-hidden focus-within:border-[#a855f7]">
                            <span class="pl-4 font-bold text-[#a855f7]">Zelle</span>
                            <input type="email" x-model="datos.zelle" class="w-full bg-transparent text-white px-3 py-3 outline-none text-sm">
                        </div>
                        <div class="flex items-center bg-black border border-gray-800 rounded-lg overflow-hidden focus-within:border-[#00D632]">
                            <span class="pl-4 font-bold text-[#00D632]">CashApp</span>
                            <input type="text" x-model="datos.cashapp" class="w-full bg-transparent text-white px-3 py-3 outline-none text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CROPPER -->
        <div x-show="cropperModalAbierto" class="fixed inset-0 bg-black/95 z-[150] flex items-center justify-center p-4" style="display: none;">
            <div class="bg-[#121214] border border-gray-800 rounded-3xl p-6 w-full max-w-md flex flex-col items-center">
                <h3 class="text-white font-bold mb-4">Encuadra tu foto de perfil</h3>
                <div class="w-full max-w-[300px] aspect-square bg-black rounded-xl overflow-hidden mb-6 relative">
                    <img id="imagen-cropper" :src="imgParaRecortar" class="max-w-full block">
                </div>
                <div class="flex gap-4 w-full">
                    <button @click="cancelarRecorte" class="flex-1 bg-gray-800 text-white font-bold py-3 rounded-xl">Cancelar</button>
                    <button @click="aplicarRecorte" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl">Confirmar Recorte</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function gestorConfiguracion() {
            // Lectura directa desde el objeto inyectado sin filtros redundantes
            const db = window.perfilCargado;

            return {
                guardando: false,
                portadaPreview: db.foto_portada || null,
                fotoPreview: db.foto_perfil || null,
                galeria: db.galeria || [],
                redes: db.redes_sociales || [],
                
                cropperModalAbierto: false,
                imgParaRecortar: '',
                
                datos: {
                    nickname: db.nickname || '',
                    nombre: db.nombre || '',
                    correo_contacto: db.correo_contacto || '',
                    profesion: db.profesion || '',
                    bio: db.bio || '',
                    telefono: db.telefono || '',
                    direccion: db.direccion || '',
                    zelle: db.zelle || '',
                    cashapp: db.cashapp || '',
                    titulo_galeria: db.titulo_galeria || 'Servicios'
                },

                agregarRed() { if(this.redes.length < 4) this.redes.push({ plataforma: 'Instagram', usuario: '' }); },
                quitarRed(index) { this.redes.splice(index, 1); },

                cargarPortada(e) {
                    let file = e.target.files[0];
                    if(!file) return;
                    let reader = new FileReader();
                    reader.onload = (event) => { this.portadaPreview = event.target.result; };
                    reader.readAsDataURL(file);
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
                                aspectRatio: 1, // Cuadrado perfecto
                                viewMode: 1,
                                dragMode: 'move', // Mueve la foto, no la caja de recorte
                                cropBoxMovable: false,
                                cropBoxResizable: false,
                                guides: false,
                                background: false
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

                cargarGaleria(e) {
                    let files = e.target.files;
                    if(!files) return;
                    let disponibles = 6 - this.galeria.length;
                    for(let i=0; i < Math.min(files.length, disponibles); i++) {
                        let reader = new FileReader();
                        reader.onload = (event) => { this.galeria.push(event.target.result); };
                        reader.readAsDataURL(files[i]);
                    }
                },
                eliminarFoto(index) { this.galeria.splice(index, 1); },

                async guardarPerfil() {
                    this.guardando = true;
                    let formData = new FormData();
                    // Editar el perfil cargado en vez de crear uno nuevo
                    formData.append('perfil_id', window.perfilCargado.id || 'null');
                    formData.append('nickname', this.datos.nickname || 'usuario');
                    formData.append('nombre', this.datos.nombre);
                    formData.append('correo_contacto', this.datos.correo_contacto);
                    formData.append('profesion', this.datos.profesion);
                    formData.append('bio', this.datos.bio);
                    formData.append('telefono', this.datos.telefono);
                    formData.append('direccion', this.datos.direccion);
                    formData.append('zelle', this.datos.zelle);
                    formData.append('cashapp', this.datos.cashapp);
                    formData.append('foto_perfil', this.fotoPreview || '');
                    formData.append('foto_portada', this.portadaPreview || '');
                    formData.append('redes_sociales', JSON.stringify(this.redes));
                    formData.append('galeria', JSON.stringify(this.galeria));

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
                            alert('¡Configuración actualizada con éxito!');
                        } else {
                            alert('Error interno:\n' + resultado.message);
                        }
                    } catch (error) { alert('Error de conexión.'); }
                    this.guardando = false;
                }
            }
        }
    </script>
@endsection