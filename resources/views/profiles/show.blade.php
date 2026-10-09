<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- El título cambia dinámicamente con el nombre del cliente desde la base de datos -->
    <title>{{ $profile->name }} - Da Un Toque</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #09090b;
            --card-glass: rgba(18, 18, 20, 0.85);
            --line: rgba(255, 255, 255, 0.08);
            --white: #ffffff;
            --slate: #a1a1aa;
            --accent-gold: #D4AF37;
            --accent-blue: #1e3a8a;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        /* Regla de Oro Bilingüe por CSS */
        body.lang-es .en-text { display: none; }
        body.lang-en .es-text { display: none; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            background-image: linear-gradient(to bottom, rgba(9, 9, 11, 0.90), rgba(9, 9, 11, 0.98)), url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--white);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background-color: var(--card-glass);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--line);
            border-radius: 30px;
            width: 100%;
            max-width: 420px;
            padding: 40px 24px 30px 24px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8);
        }
        .lang-btn {
            position: absolute; top: 24px; right: 24px;
            background: rgba(255, 255, 255, 0.1); border-radius: 12px;
            padding: 6px 12px; font-size: 11px; font-weight: 800;
            color: var(--slate); cursor: pointer; z-index: 10;
            border: 1px solid var(--line); outline: none;
        }
        .avatar-ring {
            width: 120px; height: 120px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-gold) 0%, var(--accent-blue) 100%);
            padding: 4px; margin: 0 auto 15px;
        }
        .avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; background: var(--bg-dark); border: 4px solid #121216; }
        h1 { text-align: center; font-size: 24px; margin: 0 0 4px; font-weight: 900; }
        h2 { text-align: center; font-size: 11px; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 1px; margin: 0 0 20px; font-weight: 800; }
        p.bio { text-align: center; font-size: 13px; color: var(--slate); margin: 0 0 25px; line-height: 1.6; }
        
        .btn {
            display: flex; justify-content: center; align-items: center; gap: 8px;
            text-decoration: none; border-radius: 14px; font-weight: 600; font-size: 13.5px;
            padding: 15px; color: var(--white); width: 100%; margin-bottom: 12px;
            cursor: pointer; border: none; transition: 0.2s ease; text-align: center;
        }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }
        .row { display: flex; gap: 10px; margin-bottom: 12px; }
        .btn-half { flex: 1; padding: 13px 8px; margin-bottom: 0; font-size: 13px; }
        
        .btn-ws { background-color: rgba(37, 211, 102, 0.15); color: #4ade80; border: 1px solid rgba(37, 211, 102, 0.3); }
        .btn-zelle { background-image: linear-gradient(to bottom, #8621a1, #741b8c); border: 1px solid #9127ad; font-weight: 700; }
        .btn-outline { background-color: transparent; border: 1px solid var(--line); color: var(--white); }

        /* Estilos del Modal Zelle */
        .zelle-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.85); z-index: 10001; justify-content: center; align-items: flex-end; backdrop-filter: blur(5px); }
        .zelle-modal-overlay.active { display: flex; }
        .zelle-modal-content { background-color: #121214; width: 100%; max-width: 420px; border-radius: 24px 24px 0 0; padding: 30px 24px 40px 24px; border-top: 1px solid var(--line); position: relative; }
        .close-zelle-modal { position: absolute; top: 15px; right: 20px; color: #71717a; font-size: 28px; cursor: pointer; font-weight: bold; }
        .zelle-modal-title { display: flex; align-items: center; justify-content: center; gap: 10px; color: white; font-weight: 800; font-size: 18px; margin-bottom: 15px; }
        .copy-box-success { background-color: rgba(37, 211, 102, 0.05); border: 2px dashed #25d366; border-radius: 12px; padding: 18px; margin-bottom: 20px; text-align: center; word-break: break-all; }
        .zelle-number { color: var(--accent-gold); font-size: 16px; font-weight: 900; display: block; margin-bottom: 5px; }
        .copied-text { color: #25d366; font-size: 13px; font-weight: 700; }
        
        .bank-list-col { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
        .bank-btn { display: flex; align-items: center; gap: 15px; background-color: rgba(255, 255, 255, 0.05); border: 1px solid var(--line); border-radius: 12px; padding: 12px 16px; color: var(--white); text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.2s ease; }
        .bank-icon { width: 24px; height: 24px; border-radius: 4px; object-fit: contain; }

        /* CTA Viral Loop */
        .creator-link { display: block; text-align: center; color: var(--slate); font-size: 13px; text-decoration: none; margin-top: 20px; font-weight: 600; transition: color 0.2s; }
    </style>
</head>
<body class="lang-en">

    <div class="card">
        <button class="lang-btn" id="langToggle">EN / ES</button>

        <div class="avatar-ring">
            <!-- La imagen de perfil viene de la base de datos. Si no hay, usa una por defecto -->
            <img src="{{ $profile->photo_url ?? 'default-avatar.jpg' }}" alt="{{ $profile->name }}" class="avatar">
        </div>

        <!-- Nombre extraído de la base de datos -->
        <h1>{{ $profile->name }}</h1>
        <h2>
            <!-- Cargo bilingüe (asegúrate de tener estos campos en tu base de datos, o usa uno solo) -->
            <span class="es-text">{{ $profile->role_es ?? $profile->role }}</span>
            <span class="en-text">{{ $profile->role_en ?? $profile->role }}</span>
        </h2>

        <p class="bio">
            <span class="es-text">{{ $profile->bio_es ?? $profile->bio }}</span>
            <span class="en-text">{{ $profile->bio_en ?? $profile->bio }}</span>
        </p>

        <div class="row">
            <!-- Botón de WhatsApp dinámico -->
            <a href="https://wa.me/{{ $profile->whatsapp }}" target="_blank" class="btn btn-half btn-ws">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                WhatsApp
            </a>
            
            <!-- Botón de Guardar Contacto (VCF) -->
            <a href="/vcard/{{ $profile->id }}" class="btn btn-half btn-outline">
                👤 <span class="es-text">Guardar Contacto</span><span class="en-text">Save Contact</span>
            </a>
        </div>

        <!-- Botón Zelle Dinámico (Solo se muestra si el cliente tiene Zelle configurado en la BD) -->
        @if($profile->zelle)
        <button class="btn btn-zelle" onclick="handleZelleAction(event, '{{ $profile->zelle }}')">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
            <span class="es-text">Zelle - Pagos</span><span class="en-text">Zelle - Payments</span>
        </button>
        @endif
    </div>

    <!-- CTA Viral Loop -->
    <a href="https://wa.me/573026673868?text=Estoy%20interesado%20en%20comprar%20mi%20tarjeta%20virtual." target="_blank" class="creator-link">
        <span class="es-text">Quiero mi tarjeta virtual</span>
        <span class="en-text">I want my virtual card</span>
    </a>

    <!-- Estructura del Modal de Zelle -->
    <div id="zelleModal" class="zelle-modal-overlay" onclick="closeZelleModalOut(event)">
        <div class="zelle-modal-content">
            <span class="close-zelle-modal" onclick="closeZelleModal()">&times;</span>
            
            <div class="zelle-modal-title">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                Zelle Transfer
            </div>
            
            <div class="copy-box-success">
                <span class="zelle-number" id="zelleTargetNumber">...</span>
                <span class="copied-text">
                    <span class="es-text">✓ ¡Copiado al portapapeles!</span>
                    <span class="en-text">✓ Copied to clipboard!</span>
                </span>
            </div>

            <div class="bank-list-col">
                <a href="https://www.chase.com/" target="_blank" class="bank-btn" onclick="fallbackCloseModal()">
                    <img src="https://www.google.com/s2/favicons?domain=chase.com&sz=128" alt="Chase" class="bank-icon">
                    <span class="es-text">Abrir App de Chase</span><span class="en-text">Open Chase App</span>
                </a>
                <a href="https://www.bankofamerica.com/" target="_blank" class="bank-btn" onclick="fallbackCloseModal()">
                    <img src="https://www.google.com/s2/favicons?domain=bankofamerica.com&sz=128" alt="Bank of America" class="bank-icon">
                    <span class="es-text">Abrir Bank of America</span><span class="en-text">Open Bank of America</span>
                </a>
            </div>
            <a href="zelle://" class="btn btn-zelle" style="margin-bottom:0;" onclick="fallbackCloseModal()">
                <span class="es-text">Abrir App oficial de Zelle</span>
                <span class="en-text">Open official Zelle App</span>
            </a>
        </div>
    </div>

    <!-- Scripts de la tarjeta -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const toggleBtn = document.getElementById('langToggle');
            
            // Detectar idioma del teléfono automáticamente
            const userLang = navigator.language || navigator.userLanguage;
            if (userLang && userLang.toLowerCase().startsWith('es')) {
                body.classList.replace('lang-en', 'lang-es');
            }

            // Cambiar idioma con el botón
            toggleBtn.addEventListener("click", () => {
                if (body.classList.contains('lang-es')) {
                    body.classList.replace('lang-es', 'lang-en');
                } else {
                    body.classList.replace('lang-en', 'lang-es');
                }
            });
        });

        // Lógica de Zelle con Fallback de seguridad
        const zelleModal = document.getElementById('zelleModal');
        const zelleTarget = document.getElementById('zelleTargetNumber');

        function handleZelleAction(event, accountStr) {
            event.preventDefault();
            zelleTarget.innerText = accountStr; 
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(accountStr).then(() => {
                    zelleModal.classList.add('active');
                }).catch(err => {
                    zelleModal.classList.add('active');
                });
            } else {
                let textArea = document.createElement("textarea");
                textArea.value = accountStr;
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.position = "fixed"; 
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                } catch (err) { }
                document.body.removeChild(textArea);
                zelleModal.classList.add('active');
            }
        }

        function closeZelleModal() { zelleModal.classList.remove('active'); }
        function closeZelleModalOut(event) { if (event.target === zelleModal) closeZelleModal(); }
        function fallbackCloseModal() { setTimeout(() => { closeZelleModal(); }, 1000); }
    </script>
</body>
</html>