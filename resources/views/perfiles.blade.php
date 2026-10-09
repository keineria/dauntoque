@extends('layouts.app')

@section('contenido')
    <div class="w-full max-w-6xl mx-auto" x-data="gestorPerfiles()">
        
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
                        <span class="text-white font-bold text-sm"><span x-text="perfilesUsados">1</span> de 2 Perfiles</span>
                        <div class="w-24 h-1.5 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500" :style="`width: ${(perfilesUsados/2)*100}%`"></div>
                        </div>
                    </div>
                </div>
                <!-- Botón Crear (Se desactiva si llega al límite) -->
                <button @click="crearNuevo()" :disabled="perfilesUsados >= 2" :class="perfilesUsados >= 2 ? 'opacity-50 cursor-not-allowed bg-gray-800' : 'bg-blue-600 hover:bg-blue-500 glow-accent'" class="text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo
                </button>
            </div>
        </div>

        <!-- CUADRÍCULA DE PERFILES -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Ejemplo de Tarjeta de Perfil (Generado con Alpine para la maqueta) -->
            <template x-for="perfil in listaPerfiles" :key="perfil.id">
                <div class="glass-panel border border-gray-800 rounded-3xl p-6 relative group transition-all hover:border-blue-500/50">
                    
                    <!-- Badge de Tarjeta Activa (La que suena con el NFC) -->
                    <div x-show="perfil.activoNFC" class="absolute -top-3 -right-3 bg-green-500 text-black text-[10px] font-black uppercase px-3 py-1 rounded-full shadow-[0_0_15px_rgba(0,214,50,0.4)] flex items-center gap-1 z-10">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Activo en NFC
                    </div>

                    <div class="flex items-start gap-4 mb-4">
                        <!-- Avatar Mini -->
                        <div class="w-14 h-14 rounded-full bg-gray-800 border-2 border-gray-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img x-show="perfil.foto" :src="perfil.foto" class="w-full h-full object-cover">
                            <span x-show="!perfil.foto" class="text-gray-500 font-bold" x-text="perfil.nombre.charAt(0)"></span>
                        </div>
                        
                        <div class="flex-1">
                            <h3 class="text-white font-bold text-lg leading-tight" x-text="perfil.nombrePerfil"></h3>
                            <p class="text-blue-400 text-[10px] uppercase font-bold tracking-widest mb-1" x-text="perfil.plantilla"></p>
                            <p class="text-gray-500 text-xs truncate" x-text="perfil.nombre"></p>
                        </div>
                    </div>

                    <!-- Botón Principal: Activar / Ver -->
                    <div class="flex gap-2 mt-6">
                        <button x-show="!perfil.activoNFC" @click="activarEnNFC(perfil.id)" class="flex-1 bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 rounded-xl text-xs transition-colors border border-gray-700">
                            Fijar como Activo
                        </button>
                        <a :href="'/'+nicknameGlobal" target="_blank" x-show="perfil.activoNFC" class="flex-1 bg-blue-600/20 text-blue-400 border border-blue-500/30 font-bold py-2 rounded-xl text-xs flex items-center justify-center gap-2 hover:bg-blue-600/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Ver en Vivo
                        </a>

                        <!-- Menú de Opciones (Editar, Duplicar, Eliminar) -->
                        <div class="relative" x-data="{ menuAbierto: false }">
                            <button @click="menuAbierto = !menuAbierto" @click.away="menuAbierto = false" class="w-10 h-10 bg-black border border-gray-800 rounded-xl flex items-center justify-center text-gray-400 hover:text-white hover:border-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                            </button>
                            
                            <!-- Dropdown -->
                            <div x-show="menuAbierto" x-transition class="absolute right-0 bottom-12 w-40 bg-[#121214] border border-gray-700 rounded-xl shadow-2xl py-2 z-50">
                                <button @click="editarPerfil(perfil.id)" class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white flex items-center gap-2">
                                    ✏️ Editar
                                </button>
                                <button @click="duplicarPerfil(perfil.id)" class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white flex items-center gap-2">
                                    📄 Duplicar
                                </button>
                                <hr class="border-gray-800 my-1">
                                <button @click="eliminarPerfil(perfil.id)" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-500/10 flex items-center gap-2" :disabled="perfil.activoNFC">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Tarjeta Promocional (Upsell a PRO) -->
            <div class="glass-panel border border-dashed border-gray-700 rounded-3xl p-6 flex flex-col items-center justify-center text-center opacity-70 hover:opacity-100 transition-opacity">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-white font-bold mb-1">Perfiles Ilimitados</h3>
                <p class="text-gray-400 text-xs mb-4">Crea una tarjeta para cada uno de tus negocios o proyectos.</p>
                <button class="bg-white/10 text-white border border-white/20 font-bold py-2 px-6 rounded-xl text-xs hover:bg-white hover:text-black transition-colors">Mejorar a PRO</button>
            </div>

        </div>
    </div>

    <script>
        function gestorPerfiles() {
            return {
                perfilesUsados: 1, // Esto vendrá del servidor (count)
                nicknameGlobal: 'carlos', // El nickname amarrado a la cuenta
                
                // Mockup de datos. En el futuro, esto se llenará con un fetch() a tu base de datos
                listaPerfiles: [
                    { id: 1, nombrePerfil: 'Perfil Corporativo', nombre: 'Carlos D.', plantilla: 'Executive', foto: null, activoNFC: true },
                    // { id: 2, nombrePerfil: 'Perfil Gamer', nombre: 'CarlosX', plantilla: 'Sport', foto: null, activoNFC: false }
                ],

                crearNuevo() {
                    // Redirige al editor indicando que NO debe pedir el nickname
                    window.location.href = '/editor?modo=nuevo_secundario';
                },

                editarPerfil(id) {
                    window.location.href = '/editor?id=' + id;
                },

                duplicarPerfil(id) {
                    if(this.perfilesUsados >= 2) {
                        alert("Has alcanzado el límite de 2 perfiles gratuitos.");
                        return;
                    }
                    alert("Duplicando perfil... (Enviaremos petición al servidor)");
                    // Aquí harás un fetch('/perfil/duplicar')
                },

                activarEnNFC(id) {
                    alert("¡Este perfil ahora será el que aparezca cuando escaneen tu tarjeta física!");
                    // Aquí harás fetch para intercambiar el nfc_uid
                },

                eliminarPerfil(id) {
                    if(confirm("¿Estás seguro de eliminar este perfil?")) {
                        alert("Perfil eliminado");
                    }
                }
            }
        }
    </script>
@endsection