@extends('layouts.app')

@section('contenido')
   @php
        $usuario = \Illuminate\Support\Facades\Auth::user();
        
        // 1. Identificamos el "Enlace Maestro" (el primer nickname sin sufijos)
        $perfilPrincipal = \App\Models\Profile::where('user_id', $usuario->id)->orderBy('id', 'asc')->first();
        $linkMaestro = $perfilPrincipal ? $perfilPrincipal->nickname : 'usuario';
        
        // 2. Traemos todos los perfiles y los ordenamos
        $misPerfiles = \App\Models\Profile::where('user_id', $usuario->id)
            ->orderByRaw('CASE WHEN nfc_uid IS NOT NULL AND nfc_uid != "" THEN 1 ELSE 0 END DESC')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($p, $index) use ($linkMaestro) {
                $design = json_decode($p->design_config, true);
                return [
                    'id' => $p->id,
                    'nombrePerfil' => $p->nombre_perfil ?: 'Mi Tarjeta',
                    'nombre' => $p->nombre,
                    // MAGIA: Anulamos visualmente el sufijo interno en el panel
                    'nickname' => $linkMaestro,
                    'plantilla' => $design['tema_base'] ?? 'Clásica',
                    'foto' => $p->foto_perfil,
                    'activoNFC' => ($index === 0)
                ];
            });
            
        $cantidadPerfiles = $misPerfiles->count();
    @endphp

    <div class="w-full max-w-6xl mx-auto" x-data="gestorPerfiles()">
        
        <!-- NOTIFICACIÓN TOAST (Error/Éxito) -->
        <div x-show="toastMensaje !== ''" x-transition.opacity style="display: none;" class="fixed top-6 left-1/2 transform -translate-x-1/2 z-[9999] bg-[#ffffff] rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.5)] p-4 w-[90%] max-w-sm border border-gray-300 flex flex-col gap-2">
            <p class="text-black font-bold text-xs">portal.dauntoque.com dice</p>
            <div class="flex items-start gap-2 text-gray-700 text-sm">
                <span class="text-lg leading-none" x-text="toastTipo === 'error' ? '⚠️' : '✅'"></span>
                <span x-text="toastMensaje"></span>
            </div>
            <div class="flex justify-end mt-1">
                <button @click="toastMensaje = ''" class="bg-[#3b5998] text-white font-bold py-1.5 px-5 text-sm rounded-full hover:bg-[#2d4373] transition-colors">Aceptar</button>
            </div>
        </div>

        <!-- MODAL DE CARGA LUXURY (Duplicando a pantalla completa) -->
        <div x-show="isDuplicating" x-transition.opacity class="fixed inset-0 z-[9999] bg-black/90 backdrop-blur-xl flex flex-col items-center justify-center" style="display: none;">
            <div class="relative w-24 h-24 mb-8">
                <div class="absolute inset-0 border-t-4 border-b-4 border-blue-500 rounded-full animate-spin"></div>
                <div class="absolute inset-2 border-r-4 border-l-4 border-purple-500 rounded-full animate-spin-slow"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full shadow-[0_0_30px_rgba(59,130,246,0.8)] animate-pulse"></div>
                </div>
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight mb-2 animate-pulse">Procesando...</h2>
            <p class="text-gray-400 text-sm font-light">Estamos configurando tu tarjeta inteligente.</p>
        </div>

        <!-- CABECERA Y LÍMITE DE PLAN -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-white mb-1">Mis Perfiles</h1>
                <p class="text-gray-400 text-sm">Gestiona tus diferentes facetas y decide cuál mostrar en tu tarjeta física.</p>
            </div>
            
            <div class="flex items-center gap-4 bg-black/50 border border-gray-800 p-2 rounded-2xl">
                <div class="px-4">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-1">Plan Gratuito</p>
                    <div class="flex items-center gap-2">
                        <span class="text-white font-bold text-sm"><span x-text="perfilesUsados"></span> de 2 Perfiles</span>
                        <div class="w-24 h-1.5 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500" :style="`width: ${(perfilesUsados/2)*100}%`"></div>
                        </div>
                    </div>
                </div>
                <button @click="crearNuevo()" :disabled="perfilesUsados >= 2" :class="perfilesUsados >= 2 ? 'opacity-50 cursor-not-allowed bg-gray-800' : 'bg-blue-600 hover:bg-blue-500 shadow-[0_0_15px_rgba(37,99,235,0.4)]'" class="text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg> Nuevo
                </button>
            </div>
        </div>

        <!-- CUADRÍCULA DE PERFILES REALES -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <template x-for="perfil in listaPerfiles" :key="perfil.id">
                <div class="glass-panel border border-gray-800 rounded-3xl p-6 relative group transition-all hover:border-blue-500/50 bg-[#121214]">
                    
                    <!-- Etiqueta de Tarjeta Activa -->
                    <div x-show="perfil.activoNFC" class="absolute -top-3 -right-3 bg-green-500 text-black text-[10px] font-black uppercase px-3 py-1 rounded-full shadow-[0_0_15px_rgba(0,214,50,0.4)] flex items-center gap-1 z-10">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg> Activo
                    </div>

                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-14 h-14 rounded-full bg-gray-800 border-2 border-gray-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img x-show="perfil.foto" :src="perfil.foto" class="w-full h-full object-cover">
                            <span x-show="!perfil.foto" class="text-gray-500 font-bold text-xl uppercase" x-text="perfil.nombre.charAt(0)"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-white font-bold text-lg leading-tight truncate" x-text="perfil.nombrePerfil"></h3>
                            <p class="text-blue-400 text-[10px] uppercase font-bold tracking-widest mb-1" x-text="perfil.plantilla"></p>
                            <p class="text-gray-500 text-xs truncate" x-text="perfil.nickname + '.dauntoque.com'"></p>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-6 relative">
                        <!-- Botón Activar / Ver Tarjeta -->
                        <button x-show="!perfil.activoNFC" @click="activarEnNFC(perfil.id)" class="flex-1 bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 rounded-xl text-xs transition-colors border border-gray-700">
                            Fijar Activo
                        </button>
                        <a :href="'/' + perfil.nickname" target="_blank" x-show="perfil.activoNFC" class="flex-1 bg-blue-600/20 text-blue-400 border border-blue-500/30 font-bold py-2 rounded-xl text-xs flex items-center justify-center gap-2 hover:bg-blue-600/30 transition-colors">
                            Ver Tarjeta
                        </a>

                        <!-- Dropdown de Opciones -->
                        <div class="relative" x-data="{ menuAbierto: false }">
                            <button @click="menuAbierto = !menuAbierto" @click.away="menuAbierto = false" class="w-10 h-10 bg-black border border-gray-800 rounded-xl flex items-center justify-center text-gray-400 hover:text-white hover:border-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                            </button>
                            
                            <div x-show="menuAbierto" x-transition class="absolute right-0 bottom-12 w-40 bg-[#1a1a1f] border border-gray-700 rounded-xl shadow-2xl py-2 z-50" style="display: none;">
                                <button @click="editarPerfil(perfil.id)" class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white flex items-center gap-2">✏️ Editar Info</button>
                                <button @click="editarDiseno(perfil.id)" class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white flex items-center gap-2">🎨 Editar Diseño</button>
                                <button @click="duplicarPerfil(perfil.id)" class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white flex items-center gap-2">📄 Duplicar</button>
                                <hr class="border-gray-800 my-1">
                                <button @click="eliminarPerfil(perfil.id)" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-500/10 flex items-center gap-2" :class="perfil.activoNFC ? 'opacity-50 cursor-not-allowed' : ''" :disabled="perfil.activoNFC">🗑️ Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Upsell Promocional -->
            <div class="border border-dashed border-gray-700 rounded-3xl p-6 flex flex-col items-center justify-center text-center opacity-70 hover:opacity-100 transition-opacity bg-black/20">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-white font-bold mb-1">Perfiles Ilimitados</h3>
                <p class="text-gray-400 text-xs mb-4">Crea una tarjeta para cada negocio.</p>
                <button class="bg-white/10 text-white border border-white/20 font-bold py-2 px-6 rounded-xl text-xs hover:bg-white hover:text-black transition-colors">Mejorar a PRO</button>
            </div>

        </div>
    </div>

    <script>
        function gestorPerfiles() {
            return {
                perfilesUsados: {{ $cantidadPerfiles }}, 
                listaPerfiles: {!! json_encode($misPerfiles) !!}, 
                
                isDuplicating: false,
                toastMensaje: '',
                toastTipo: 'success', 

                mostrarToast(mensaje, tipo = 'error') {
                    this.toastMensaje = mensaje;
                    this.toastTipo = tipo;
                },

                crearNuevo() {
                    window.location.href = '/editor?modo=nuevo_secundario';
                },

                editarPerfil(id) {
                    window.location.href = '/editor?id=' + id;
                },

                editarDiseno(id) {
                    window.location.href = '/editor?id=' + id + '&step=8';
                },

                async duplicarPerfil(id) {
                    if(this.perfilesUsados >= 2) {
                        this.mostrarToast("Límite de 2 perfiles gratuitos alcanzado. Mejora a PRO.", "error");
                        return;
                    }
                    
                    this.isDuplicating = true;

                    try {
                        let respuesta = await fetch(`/perfil/${id}/duplicar`, {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        let data = await respuesta.json();
                        
                        setTimeout(() => {
                            if (data.status === 'success') {
                                window.location.reload(); 
                            } else {
                                this.isDuplicating = false;
                                this.mostrarToast(data.message, "error");
                            }
                        }, 1500);
                    } catch (error) {
                        this.isDuplicating = false;
                        this.mostrarToast("Error de conexión al duplicar.", "error");
                    }
                },

                // === EL VERDADERO MOTOR PARA ACTIVAR NFC ===
                async activarEnNFC(id) {
                    this.isDuplicating = true; // Usamos el modal de carga para que se vea elegante

                    try {
                        let respuesta = await fetch(`/perfil/${id}/activar`, {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        let data = await respuesta.json();
                        
                        setTimeout(() => {
                            if (data.status === 'success') {
                                window.location.reload(); // Recarga y mueve la etiqueta verde de Activo
                            } else {
                                this.isDuplicating = false;
                                this.mostrarToast(data.message, "error");
                            }
                        }, 1500);
                    } catch (error) {
                        this.isDuplicating = false;
                        this.mostrarToast("Error conectando con el servidor.", "error");
                    }
                },

               async eliminarPerfil(id) {
                    if(confirm("¿Estás seguro de eliminar permanentemente este perfil? Esta acción no se puede deshacer.")) {
                        
                        this.isDuplicating = true; // Usamos el modal de carga para bloquear la pantalla

                        try {
                            let respuesta = await fetch(`/perfil/${id}/eliminar`, {
                                method: 'DELETE',
                                headers: { 
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            });
                            
                            let data = await respuesta.json();
                            
                            setTimeout(() => {
                                if (data.status === 'success') {
                                    window.location.reload(); // Recarga la página y la tarjeta desaparece
                                } else {
                                    this.isDuplicating = false;
                                    this.mostrarToast(data.message, "error");
                                }
                            }, 1000);

                        } catch (error) {
                            this.isDuplicating = false;
                            this.mostrarToast("Error conectando con el servidor.", "error");
                        }
                    }
                }
            }
        }
    </script>
@endsection