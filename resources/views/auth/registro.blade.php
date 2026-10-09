<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activa tu Cuenta - DaUnToque</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #ffffff; overflow: hidden; }
        .glass-panel { background: rgba(18, 18, 22, 0.7); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.05); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
    </style>
</head>
<body class="h-screen w-screen flex items-center justify-center relative">
    
    <!-- Luces de ambiente -->
    <div class="absolute top-[10%] left-[20%] w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-30 animate-pulse"></div>
    <div class="absolute bottom-[10%] right-[20%] w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-30 animate-pulse"></div>

    <div class="glass-panel w-full max-w-md p-10 rounded-3xl relative z-10 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 mx-auto flex items-center justify-center font-black text-white text-3xl mb-6 shadow-[0_0_30px_rgba(59,130,246,0.4)]">D</div>
        
        <h1 class="text-2xl font-black tracking-tight mb-2">Bienvenido a DaUnToque</h1>
        <p class="text-sm text-gray-400 mb-8">Valida tus datos para comenzar a crear tu tarjeta de negocio virtual.</p>

        <form action="/dashboard" method="GET" class="space-y-4 text-left">
            <div>
                <label class="block text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2">Código de Activación</label>
                <input type="text" value="{{ $token }}" readonly class="w-full bg-white/5 border border-gray-700 rounded-xl px-4 py-3 text-gray-400 text-sm outline-none cursor-not-allowed font-mono">
            </div>
            <div>
                <label class="block text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2">Nombre Completo</label>
                <input type="text" placeholder="Ej. Carlos Pino" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 text-white text-sm outline-none focus:border-blue-500 transition-colors">
            </div>
            <div>
                <label class="block text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2">Crea una Contraseña</label>
                <input type="password" placeholder="••••••••" class="w-full bg-black border border-gray-800 rounded-xl px-4 py-3 text-white text-sm outline-none focus:border-blue-500 transition-colors">
            </div>

            <button type="submit" class="w-full mt-6 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-500 hover:to-blue-700 text-white font-bold py-4 rounded-xl shadow-[0_0_20px_rgba(59,130,246,0.4)] transition-all">
                Crear Mi Cuenta
            </button>
        </form>
    </div>
</body>
</html>