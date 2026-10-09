@extends('layouts.app')

@section('contenido')
    <!-- Métricas Principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="metric-card p-6 rounded-3xl">
            <h3 class="text-4xl font-black text-white mb-2">0</h3>
            <p class="text-sm text-gray-400">Tags / Vistas de Perfil</p>
        </div>
        <div class="metric-card p-6 rounded-3xl">
            <h3 class="text-4xl font-black text-green-500 mb-2">0</h3>
            <p class="text-sm text-gray-400">Contactos Guardados</p>
        </div>
        <div class="metric-card p-6 rounded-3xl">
            <h3 class="text-4xl font-black text-blue-500 mb-2">0</h3>
            <p class="text-sm text-gray-400">Chats de WhatsApp</p>
        </div>
        <div class="metric-card p-6 rounded-3xl">
            <h3 class="text-4xl font-black text-purple-500 mb-2">0</h3>
            <p class="text-sm text-gray-400">Clics en Pagos</p>
        </div>
    </div>

    <!-- Cabecera de Sección -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Mis Tarjetas Virtuales</h2>
            <p class="text-sm text-gray-400">Gestiona tus perfiles activos.</p>
        </div>
        <!-- El botón pequeño superior solo se muestra si ya tienen un perfil creado -->
        @if($perfil)
            <a href="/editor" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition-colors border border-gray-700">+ Nuevo Perfil</a>
        @endif
    </div>

    <!-- Lógica de Estado Cero vs Tarjeta Activa -->
    @if(!$perfil)
        <!-- ZERO STATE: CTA Gigante para Usuarios Nuevos -->
        <div class="metric-card border-dashed border-2 border-gray-700 rounded-3xl p-12 flex flex-col items-center justify-center text-center group hover:border-blue-500 transition-colors">
            <div class="w-20 h-20 bg-blue-600/10 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Aún no tienes un perfil digital</h3>
            <p class="text-gray-400 mb-8 max-w-md">Crea tu identidad virtual en segundos con la ayuda de nuestra IA y prepárate para enlazar tu tarjeta física inteligente.</p>
            <a href="/editor" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 px-8 rounded-2xl transition-all shadow-[0_0_20px_rgba(59,130,246,0.4)] flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Crear mi Perfil y Activar Tarjeta
            </a>
        </div>
    @else
        <!-- TARJETA ACTIVA: Vista de gestión -->
        <div class="metric-card rounded-3xl p-6 flex items-center justify-between border-l-4 border-l-blue-500">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-full bg-gray-900 overflow-hidden border-2 border-gray-700">
                    <img src="{{ $perfil->foto_perfil ?? 'https://ui-avatars.com/api/?name='.urlencode($perfil->nombre).'&background=1e3a8a&color=fff' }}" class="w-full h-full object-cover">
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">{{ $perfil->nombre ?? 'Sin Nombre' }}</h3>
                    <p class="text-sm text-blue-400 font-mono">{{ $perfil->nickname ? $perfil->nickname . '.dauntoque.com' : 'Sin enlace activo' }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="https://{{ $perfil->nickname }}.dauntoque.com" target="_blank" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold rounded-xl transition-colors border border-gray-700">Ver Web</a>
                <a href="/dashboard/configuracion" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition-colors shadow-[0_0_15px_rgba(59,130,246,0.3)]">Editar Perfil</a>
            </div>
        </div>
    @endif
    
    <!-- MODAL LUXURY DE ACTIVACIÓN NFC -->
    @if(session('nfc_success'))
        <!-- Librería de Confeti -->
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
        
        <div x-data="{ showModalNFC: true }" x-show="showModalNFC" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 backdrop-blur-xl bg-black/80" style="display: none;">
            
            <div class="relative bg-gradient-to-b from-[#1a1a1f] to-[#0a0a0d] border border-gray-700/50 rounded-[40px] p-10 w-full max-w-lg text-center shadow-[0_0_80px_rgba(59,130,246,0.2)] overflow-hidden" 
                 x-show="showModalNFC" 
                 x-transition:enter="transition ease-out duration-700" 
                 x-transition:enter-start="opacity-0 translate-y-10 scale-90" 
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                
                <!-- Efecto de luz trasera -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-blue-600/30 rounded-full blur-[80px] pointer-events-none"></div>

                <!-- Icono Flotante -->
                <div class="relative w-32 h-32 mx-auto mb-6">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-spin-slow opacity-20 blur-xl"></div>
                    <div class="relative w-full h-full bg-gray-900 border-2 border-gray-700 rounded-3xl shadow-2xl flex items-center justify-center rotate-3 hover:rotate-0 transition-transform">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.286 14.434A5.964 5.964 0 0112 13a5.964 5.964 0 013.714 1.434M10.143 11.217A2.982 2.982 0 0112 10.5a2.982 2.982 0 011.857.717"></path></svg>
                    </div>
                </div>

                <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 mb-4 tracking-tight">¡Magia Pura!</h2>
                <p class="text-gray-300 text-lg mb-8 font-light">Tu tarjeta <span class="font-bold text-white">DaUnToque</span> ha sido activada y vinculada a tu perfil digital de por vida.</p>
                
                <button @click="showModalNFC = false" class="w-full bg-white text-black font-black py-4 rounded-2xl text-lg hover:scale-105 transition-all shadow-[0_10px_30px_rgba(255,255,255,0.2)]">
                    Comenzar a usar mi panel
                </button>
            </div>
        </div>

        <script>
            // Detonamos el confeti medio segundo después de cargar la página
            setTimeout(() => {
                var duration = 3 * 1000;
                var animationEnd = Date.now() + duration;
                var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10000 };

                function randomInRange(min, max) { return Math.random() * (max - min) + min; }

                var interval = setInterval(function() {
                    var timeLeft = animationEnd - Date.now();
                    if (timeLeft <= 0) { return clearInterval(interval); }
                    var particleCount = 50 * (timeLeft / duration);
                    
                    // Dispara confeti desde la izquierda y derecha
                    confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }, colors: ['#3b82f6', '#8b5cf6', '#ffffff'] }));
                    confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }, colors: ['#10b981', '#f59e0b', '#ffffff'] }));
                }, 250);
            }, 500);
        </script>
    @endif
    
@endsection