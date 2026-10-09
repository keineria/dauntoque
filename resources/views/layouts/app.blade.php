<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - DaUnToque</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #ffffff; }
        .glass-panel { background: rgba(20, 20, 25, 0.6); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .metric-card { background: linear-gradient(145deg, rgba(30,30,35,0.8) 0%, rgba(15,15,18,0.8) 100%); border: 1px solid rgba(255,255,255,0.03); transition: transform 0.3s ease, border-color 0.3s ease; }
        .metric-card:hover { transform: translateY(-5px); border-color: rgba(59, 130, 246, 0.4); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .text-pastel-green { color: #4ade80; }
        .bg-pastel-green { background-color: #4ade80; }
    </style>
</head>
<body class="h-screen w-screen flex overflow-hidden" 
      x-data="{ 
          modalCuentaAbierto: false, 
          nombreUsuario: {{ Js::from(Auth::user()->name ?? 'Administrador') }},
          correoUsuario: {{ Js::from(Auth::user()->email ?? 'admin@dauntoque.com') }}
      }">
    <!-- SISTEMA DE NOTIFICACIONES FLOTANTES (TOASTS) -->
    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="fixed top-6 right-6 z-[9999] bg-[#00D632]/20 border border-[#00D632]/50 backdrop-blur-md text-white px-6 py-4 rounded-2xl shadow-[0_0_30px_rgba(0,214,50,0.2)] flex items-center gap-3">
            <svg class="w-6 h-6 text-[#00D632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="font-bold text-sm tracking-wide">{{ session('status') }}</span>
            <button @click="show = false" class="ml-4 text-white hover:text-gray-300 transition-colors">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 7000)" class="fixed top-6 right-6 z-[9999] bg-red-500/20 border border-red-500/50 backdrop-blur-md text-white px-6 py-4 rounded-2xl shadow-[0_0_30px_rgba(239,68,68,0.2)] flex items-center gap-3">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm tracking-wide">{{ session('error') }}</span>
            <button @click="show = false" class="ml-4 text-white hover:text-gray-300 transition-colors">&times;</button>
        </div>
    @endif

    <div class="fixed top-[-20%] left-[-10%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[500px] h-[500px] bg-purple-600/10 rounded-full blur-[120px] pointer-events-none"></div>

    <aside class="w-64 h-full glass-panel border-r border-white/5 flex flex-col relative z-20">
        <div class="p-6 border-b border-white/5 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center font-bold text-white shadow-lg">D</div>
            <span class="font-extrabold text-xl tracking-tight">dauntoque</span>
        </div>
        
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto hide-scrollbar">
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->is('dashboard') ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                Dashboard
            </a>
            <a href="/dashboard/perfiles" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->is('dashboard/perfiles') ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                Mis Perfiles
            </a>
            <a href="/dashboard/analiticas" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->is('dashboard/analiticas') ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Analíticas Avanzadas
            </a>
            <a href="/dashboard/configuracion" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-colors {{ request()->is('dashboard/configuracion') ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Configuración
            </a>
        </nav>
        
        <div class="p-4 border-t border-white/5">
            <!-- Formulario de cierre de sesión seguro conectado a Laravel Breeze -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-colors font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 h-full overflow-y-auto hide-scrollbar relative z-10 p-8 lg:p-12">
        
        <header class="flex justify-between items-center mb-10 relative">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight mb-1" x-text="`Hola, ${nombreUsuario.split(' ')[0]} 👋`"></h1>
                <p class="text-gray-400 text-sm">Tu centro de mando digital.</p>
            </div>
            
            <div @click="modalCuentaAbierto = !modalCuentaAbierto" class="flex items-center gap-3 cursor-pointer group bg-white/5 border border-white/10 rounded-full pl-2 pr-4 py-1.5 hover:bg-white/10 transition-colors relative z-50">
                <div class="w-10 h-10 rounded-full border-2 border-blue-500 overflow-hidden shadow-[0_0_15px_rgba(59,130,246,0.3)] group-hover:scale-105 transition-transform">
                    <img :src="`https://ui-avatars.com/api/?name=${nombreUsuario.replace(' ', '+')}&background=1e3a8a&color=fff`" alt="Avatar">
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-white leading-tight" x-text="nombreUsuario"></p>
                    <p class="text-[10px] text-gray-400 font-mono uppercase tracking-wider">Ajustes de cuenta</p>
                </div>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="modalCuentaAbierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>

            <div x-show="modalCuentaAbierto" @click.away="modalCuentaAbierto = false" x-transition.opacity class="absolute top-16 right-0 w-80 glass-panel border border-gray-700 p-6 rounded-2xl shadow-2xl z-50" style="display: none;">
                <h3 class="text-white font-bold mb-4 border-b border-gray-700 pb-2">Datos de Acceso</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] text-gray-500 font-bold uppercase">Nombre del Titular</label>
                        <input type="text" x-model="nombreUsuario" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-2 text-white text-sm outline-none focus:border-blue-500 mt-1" disabled>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 font-bold uppercase">Correo de Ingreso</label>
                        <input type="email" x-model="correoUsuario" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-2 text-white text-sm outline-none focus:border-blue-500 mt-1" disabled>
                    </div>
                </div>
            </div>
        </header>

        @yield('contenido')

    </main>
</body>
</html>