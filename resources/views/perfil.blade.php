<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Título de la pestaña web generado dinámicamente con el nombre del cliente -->
    <title>{{ $profile->name }} - Da Un Toque</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #09090b;
            --card-glass: rgba(18, 18, 20, 0.85);
            --line: rgba(255, 255, 255, 0.08);
            --white: #ffffff;
            --slate: #a1a1aa;
            --accent-gold: #f59e0b;
            --accent-blue: #3b82f6;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif; background-color: var(--bg-dark);
            background-image: linear-gradient(to bottom, rgba(9, 9, 11, 0.90), rgba(9, 9, 11, 0.98)), url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop');
            background-size: cover; background-position: center; background-attachment: fixed;
            color: var(--white); display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; padding: 20px;
        }
        .card {
            background-color: var(--card-glass); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--line); border-radius: 30px; width: 100%; max-width: 420px; padding: 40px 24px 30px 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8);
        }
        .avatar-ring {
            width: 150px; height: 150px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-gold) 0%, var(--accent-blue) 100%);
            padding: 5px; margin: 0 auto 20px;
        }
        .avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; background: var(--bg-dark); border: 4px solid #121216; }
        h1 { text-align: center; font-size: 26px; margin: 0 0 6px; font-weight: 900; }
        h2 { text-align: center; font-size: 12px; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 1px; margin: 0 0 15px; font-weight: 800; }
        p.bio { text-align: center; font-size: 13.5px; color: var(--slate); margin: 0 0 25px; line-height: 1.6; }
        .btn {
            display: flex; justify-content: center; align-items: center; gap: 8px; text-decoration: none; border-radius: 14px;
            font-weight: 600; font-size: 13.5px; padding: 15px; color: var(--white); width: 100%; margin-bottom: 12px; cursor: pointer; border: none; transition: 0.2s ease;
        }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }
        .row { display: flex; gap: 10px; margin-bottom: 12px; }
        .btn-half { flex: 1; padding: 13px 8px; margin-bottom: 0; font-size: 13px; }
        
        /* Paleta de botones */
        .btn-ws { background-color: rgba(37, 211, 102, 0.15); color: #4ade80; border: 1px solid rgba(37, 211, 102, 0.3); }
        .btn-sms { background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .btn-ig { background: linear-gradient(135deg, rgba(225, 48, 108, 0.15) 0%, rgba(131, 58, 180, 0.15) 100%); color: #f472b6; border: 1px solid rgba(225, 48, 108, 0.3); }
        .btn-zelle { background-image: linear-gradient(to bottom, #8621a1, #741b8c); border: 1px solid #9127ad; font-weight: 700; }
        .btn-outline { background-color: transparent; border: 1px solid var(--line); color: var(--white); }
        .creator-link { display: block; text-align: center; color: var(--slate); font-size: 13px; text-decoration: none; margin-top: 20px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="card">
        <div class="avatar-ring">
            <!-- Renderiza la foto desde la base de datos o muestra una predeterminada si está en blanco -->
            <img src="{{ $profile->photo_url ?? '/img/default.jpg' }}" alt="{{ $profile->name }}" class="avatar">
        </div>

        <!-- Renderiza los textos principales de la base de datos -->
        <h1>{{ $profile->name }}</h1>
        <h2>{{ $profile->role }}</h2>
        
        @if($profile->bio)
            <p class="bio">{{ $profile->bio }}</p>
        @endif

        <!-- Renderiza los botones de redes sociales dinámicamente -->
        @if($profile->instagram)
        <a href="https://instagram.com/{{ $profile->instagram }}" target="_blank" class="btn btn-ig">
            📸 Seguime en Instagram
        </a>
        @endif

        <div class="row">
            @if($profile->whatsapp)
            <a href="https://wa.me/{{ $profile->whatsapp }}" target="_blank" class="btn btn-half btn-ws">
                💬 WhatsApp
            </a>
            @endif
            
            <!-- Si tienes un campo 'phone' guardado, genera el botón de Mensaje (SMS) -->
            @if($profile->phone)
            <a href="sms:{{ $profile->phone }}" class="btn btn-half btn-sms">
                ✉️ Mensaje
            </a>
            @endif
        </div>

        <!-- Botón para descargar archivo de contacto vCard (.vcf) autogenerado por Laravel -->
        <a href="/vcard/{{ $profile->id }}" class="btn btn-outline">
            👤 Guardar Contacto
        </a>

        <!-- Botón Zelle Dinámico -->
        @if($profile->zelle)
        <button class="btn btn-zelle" onclick="alert('Tu Zelle es: {{ $profile->zelle }} (Aquí enlazaremos el modal completo luego)')">
            🟣 Zelle: {{ $profile->zelle }}
        </button>
        @endif
    </div>

    <!-- Enlace 'Viral Loop' para conseguirte más clientes -->
    <a href="https://wa.me/573026673868?text=Estoy%20interesado%20en%20comprar%20mi%20tarjeta%20virtual." target="_blank" class="creator-link">
        Quiero mi tarjeta virtual
    </a>

</body>
</html>