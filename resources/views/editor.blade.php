<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor de perfil · DaUnToque</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.panel-estilos')

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <style>
        /* Editor: formulario a la izquierda con el estilo del panel, vista previa del perfil a la derecha */
        .ed-app { display:flex; height:100vh; overflow:hidden; }
        .ed-form { width:50%; min-width:0; display:flex; flex-direction:column; background:var(--bg); }
        .ed-top { height:56px; flex:none; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:0 24px; border-bottom:1px solid var(--border); }
        .ed-back { display:inline-flex; align-items:center; gap:6px; color:var(--muted); text-decoration:none; font-size:13.5px; font-weight:600; }
        .ed-back:hover { color:var(--text); }
        .ed-body { flex:1; overflow-y:auto; display:flex; }
        .ed-steps { width:200px; flex:none; padding:24px 12px 24px 20px; display:flex; flex-direction:column; gap:2px; }
        .ed-step { display:flex; align-items:center; gap:10px; padding:7px 10px; border-radius:6px; font-size:13px; color:var(--muted); border:0; background:none; text-align:left; width:100%; }
        .ed-step i { width:20px; height:20px; border-radius:50%; border:1.5px solid var(--border); display:grid; place-items:center; font-style:normal; font-size:10.5px; font-weight:700; flex:none; }
        .ed-step.on { background:var(--surface); color:var(--text); font-weight:600; }
        .ed-step.on i { border-color:var(--primary); color:var(--primary); }
        .ed-step.done { color:var(--text); cursor:pointer; }
        .ed-step.done i { background:var(--primary); border-color:var(--primary); color:#fff; }
        .ed-step.done:hover { background:var(--surface); }
        .ed-main { flex:1; min-width:0; padding:28px 28px 120px; max-width:600px; }
        .ed-bar { height:5px; border-radius:9px; background:var(--surface); overflow:hidden; margin:14px 0 22px; }
        .ed-bar span { display:block; height:100%; background:var(--primary); transition:width .4s; }
        .ed-foot { flex:none; border-top:1px solid var(--border); padding:12px 24px; display:flex; justify-content:space-between; gap:10px; background:var(--bg); }
        .ed-opt { height:38px; border:1px solid var(--border); border-radius:7px; background:var(--card); color:var(--muted); font-size:13px; font-weight:600; cursor:pointer; }
        .ed-opt.on { border-color:var(--primary); background:var(--primary-soft); color:var(--primary); }
        .ed-drop { border:1.5px dashed var(--border); border-radius:10px; background:var(--surface); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; padding:18px; color:var(--muted); font-size:13px; cursor:pointer; position:relative; text-align:center; }
        .ed-drop:hover { border-color:var(--primary); color:var(--text); }
        .ed-red { border:1px solid var(--border); border-radius:8px; background:var(--card); padding:8px 4px; display:flex; flex-direction:column; align-items:center; gap:4px; font-size:10px; font-weight:600; color:var(--muted); cursor:pointer; text-transform:uppercase; letter-spacing:.04em; }
        .ed-red:hover { border-color:var(--primary); color:var(--text); }
        .ed-red svg, .ed-ico-red svg { width:18px; height:18px; }
        .ed-preview { width:50%; display:flex; align-items:center; justify-content:center; background:var(--surface); border-left:1px solid var(--border); position:relative; }
        .ed-preview-label { position:absolute; top:18px; left:24px; font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; }

        @media (max-width: 900px) {
            .ed-steps { display:none; }
        }
        @media (max-width: 768px) {
            .ed-app { height:100dvh; }
            .ed-form { width:100%; }
            .ed-main { padding:20px 16px 120px; }
            .ed-top, .ed-foot { padding-left:16px; padding-right:16px; }
            .ed-preview { display:none; }
            .ed-preview.abierta { display:flex; position:fixed; inset:0; z-index:200; width:100%; flex-direction:column; }
        }
        .phone-mockup { 
            width: 320px; height: 650px; border: 12px solid #1a1a1c; border-radius: 40px; 
            background-color: #09090b; position: relative; overflow: hidden; 
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.8), inset 0 0 15px rgba(255,255,255,0.05); 
        }
        .phone-notch { 
            position: absolute; top: 0; left: 50%; transform: translateX(-50%); 
            width: 120px; height: 25px; background-color: #1a1a1c; 
            border-bottom-left-radius: 15px; border-bottom-right-radius: 15px; z-index: 50; 
        }
        
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .btn-zelle { background-image: linear-gradient(to bottom, #8621a1, #741b8c); border: 1px solid #9127ad; color: white;}
        .btn-cashapp { background-color: #00D632; border: 1px solid #00ea36; color: black; }
        .btn-venmo { background-color: #008CFF; border: 1px solid #009dff; color: white;}
        
        /* ====================================================
           SISTEMA DE PLANTILLAS MODULARES (LIVE PREVIEW)
        ==================================================== */
        .tpl-crystal { 
            background: rgba(20, 20, 25, 0.4); backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); 
            border-radius: 24px; padding: 20px 15px; margin: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
        }
        .tpl-crystal .accent-text { color: var(--color-primario, #3b82f6); }
        .tpl-crystal .avatar-ring { border-color: #121216; }

        .tpl-matte { 
            background-color: #0d0d0d; border: 1px solid rgba(255,255,255,0.03); 
            border-radius: 24px; padding: 20px 15px; margin: 15px; box-shadow: inset 0 0 20px rgba(0,0,0,1); 
        }
        .tpl-matte .accent-text { color: var(--color-primario, #9ca3af); }
        .tpl-matte .avatar-ring { border-color: #27272a; }

        .tpl-luxury { 
            background-color: #121212; border: 1px solid var(--color-primario, #D4AF37); 
            border-radius: 24px; padding: 20px 15px; margin: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.8), inset 0 0 20px rgba(212, 175, 55, 0.05); 
        }
        .tpl-luxury .accent-text { color: var(--color-primario, #D4AF37); font-family: serif; }
        .tpl-luxury .avatar-ring { border-color: var(--color-primario, #D4AF37); padding: 2px; }
        .tpl-luxury h2 { font-family: serif; }

        .tpl-corporativo { 
            background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; 
            padding: 20px 15px; margin: 15px; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); 
        }
        .tpl-corporativo h2 { color: #0f172a; font-family: serif; font-weight: 700; }
        .tpl-corporativo p { color: #475569; }
        .tpl-corporativo .accent-text { color: var(--color-primario, #0f172a); }
        .tpl-corporativo .avatar-ring { border-color: #e2e8f0; background: white; padding: 4px; }

        .tpl-sport { 
            background-color: #111; border-left: 4px solid var(--color-primario, #ccff00); 
            border-radius: 12px; padding: 20px 15px; margin: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.8); 
        }
        .tpl-sport h2 { color: white; text-transform: uppercase; letter-spacing: 1px; font-weight: 900; font-family: monospace; }
        .tpl-sport p { color: #9ca3af; }
        .tpl-sport .accent-text { color: var(--color-primario, #ccff00); }
        .tpl-sport .avatar-ring { border-color: var(--color-primario, #ccff00); border-width: 2px; padding: 2px; box-shadow: 0 0 15px rgba(204,255,0,0.2); }

        .tpl-minimalista { 
            background-color: #fafafa; border: 1px solid #eaeaea; border-radius: 20px; 
            padding: 20px 15px; margin: 15px; 
        }
        .tpl-minimalista h2 { color: #111111; font-weight: 600; }
        .tpl-minimalista p { color: #666666; font-weight: 300; }
        .tpl-minimalista .accent-text { color: var(--color-primario, #111111); }
        .tpl-minimalista .avatar-ring { border-color: #cccccc; padding: 2px; }
    </style>
</head>
<body x-data="perfilDigital()">

    {{-- Aviso de error (falta un dato, fallo del servidor) --}}
    <div x-show="toastError !== ''" x-transition.opacity x-cloak class="dt-panel" role="alert" style="position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;width:calc(100% - 32px);max-width:400px;box-shadow:0 12px 32px rgba(0,0,0,.18)">
        <p style="margin:0 0 4px;font-weight:600;color:var(--danger)">Revisa este paso</p>
        <p class="dt-sub" style="margin:0 0 14px" x-text="toastError"></p>
        <div style="display:flex;justify-content:flex-end"><button @click="toastError = ''" class="dt-btn pri">Entendido</button></div>
    </div>

    <div class="ed-app">
        <section class="ed-form">
            <header class="ed-top">
                <a href="/dashboard/perfiles" class="ed-back">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>Mis perfiles
                </a>
                <span class="dt-chip" x-text="modoActual === 'editar' ? 'Editando perfil' : 'Nuevo perfil'"></span>
            </header>

            <div class="ed-body">
                {{-- Lista de pasos --}}
                <nav class="ed-steps" aria-label="Pasos">
                    <template x-for="n in totalPasos" :key="n">
                        <button class="ed-step" x-show="!(n === 1 && modoActual === 'editar')"
                                :class="n === paso ? 'on' : (n < paso ? 'done' : '')"
                                @click="if (n < paso) paso = n" :aria-current="n === paso ? 'step' : null">
                            <i x-text="n < paso ? '✓' : n"></i><span x-text="titulos[n]"></span>
                        </button>
                    </template>
                </nav>

                <div class="ed-main">
                    <p class="dt-label" style="margin:0" x-text="`Paso ${paso} de ${totalPasos}`"></p>
                    <h1 class="dt-h1" style="margin-top:2px" x-text="titulos[paso]"></h1>
                    <p class="dt-sub" x-text="subtitulos[paso]"></p>
                    <div class="ed-bar"><span :style="`width:${progreso}%`"></span></div>

                    <div class="dt-panel">
                        {{-- PASO 1: usuario (solo al crear) --}}
                        <div x-show="paso === 1 && modoActual === 'crear'">
                            <label class="dt-label" for="ed-nick">Tu usuario único</label>
                            <div style="display:flex;gap:8px">
                                <div class="dt-inline" style="flex:1"><span>portal.dauntoque.com/</span><input id="ed-nick" type="text" x-model="nickname" @input="nickValidado = false; nickError = ''" placeholder="carlos" style="text-transform:lowercase"></div>
                                <button @click="validarNickname" class="dt-btn" style="height:40px">
                                    <span x-show="!validandoNick">Validar</span><span x-show="validandoNick">…</span>
                                </button>
                            </div>
                            <p x-show="nickValidado" style="margin:8px 0 0;font-size:12.5px;font-weight:600;color:var(--success)">✓ Usuario disponible</p>
                            <p x-show="nickError !== ''" style="margin:8px 0 0;font-size:12.5px;font-weight:600;color:var(--danger)" x-text="nickError"></p>
                        </div>

                        {{-- PASO 2: tipo, foto y nombre --}}
                        <div x-show="paso === 2" x-cloak>
                            <span class="dt-label">¿Qué uso le darás a tu tarjeta?</span>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:18px">
                                <button @click="tipoPerfil = 'corporativo'" class="ed-opt" :class="tipoPerfil === 'corporativo' ? 'on' : ''">Corporativo</button>
                                <button @click="tipoPerfil = 'empresa'" class="ed-opt" :class="tipoPerfil === 'empresa' ? 'on' : ''">Empresa</button>
                                <button @click="tipoPerfil = 'hobbies'" class="ed-opt" :class="tipoPerfil === 'hobbies' ? 'on' : ''">Hobbies</button>
                            </div>
                            <span class="dt-label">Foto de perfil</span>
                            <label class="ed-drop" style="margin-bottom:18px">
                                <input type="file" accept="image/*" @change="abrirRecorte" style="display:none">
                                <template x-if="!fotoPreview"><span>Subir foto</span></template>
                                <template x-if="fotoPreview"><img :src="fotoPreview" alt="" style="width:76px;height:76px;object-fit:cover;border-radius:50%;border:2px solid var(--primary)"></template>
                                <template x-if="fotoPreview"><span style="font-size:12px">Cambiar foto</span></template>
                            </label>
                            <label class="dt-label" for="ed-nombre">Tu nombre público</label>
                            <input id="ed-nombre" type="text" x-model="nombre" placeholder="Tu nombre completo" class="dt-input">
                        </div>

                        {{-- PASO 3: profesión --}}
                        <div x-show="paso === 3" x-cloak>
                            <label class="dt-label" for="ed-prof">Título o profesión</label>
                            <input id="ed-prof" type="text" x-model="profesion" placeholder="ej. Consultor de negocios" class="dt-input">
                        </div>

                        {{-- PASO 4: biografía con IA --}}
                        <div x-show="paso === 4" x-cloak>
                            <label class="dt-label" for="ed-bio">Descripción del perfil</label>
                            <textarea id="ed-bio" x-model="bio" rows="5" class="dt-input" placeholder="Escribe a qué te dedicas o deja que la IA lo redacte."></textarea>
                            <div style="display:flex;justify-content:flex-end;margin-top:10px">
                                <button @click="mejorarConIA" :disabled="generandoIA" class="dt-btn" style="border-color:#c9b8f5;background:#f4effe;color:#6b3fd6">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/></svg>
                                    <span x-show="!generandoIA">Mejorar con IA</span><span x-show="generandoIA">Redactando…</span>
                                </button>
                            </div>
                        </div>

                        {{-- PASO 5: galería --}}
                        <div x-show="paso === 5" x-cloak>
                            <span class="dt-label">Portafolio o productos (máx. 4)</span>
                            <div class="ed-drop" x-show="galeriaPreviews.length < 4" style="margin-bottom:12px">
                                <input type="file" multiple accept="image/*" @change="procesarImagenesGaleria($event)" style="position:absolute;inset:0;opacity:0;cursor:pointer" aria-label="Cargar imágenes">
                                <span>Cargar imágenes</span>
                                <small><span x-text="4 - galeriaPreviews.length"></span> disponibles</small>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px" x-show="galeriaPreviews.length > 0">
                                <template x-for="(img, index) in galeriaPreviews" :key="index">
                                    <div style="position:relative">
                                        <img :src="img" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
                                        <button @click="eliminarImagenGaleria(index)" class="dt-ico" style="position:absolute;top:-8px;right:-8px;width:24px;height:24px;border-radius:50%" aria-label="Quitar imagen">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- PASO 6: contacto y redes --}}
                        <div x-show="paso === 6" x-cloak>
                            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px">
                                <div class="dt-inline"><span>Teléfono</span><input type="tel" x-model="telefono" placeholder="Teléfono / WhatsApp" aria-label="Teléfono"></div>
                                <div class="dt-inline"><span>Correo</span><input type="email" x-model="correo" placeholder="Correo electrónico" aria-label="Correo"></div>
                                <div class="dt-inline"><span>Dirección</span><input type="text" x-model="direccion" placeholder="Opcional" aria-label="Dirección"></div>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center"><span class="dt-label">Redes sociales</span><span class="dt-chip" x-text="`${redesSeleccionadas.length}/4`"></span></div>
                            <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:6px;margin-bottom:12px" :style="redesSeleccionadas.length >= 4 ? 'opacity:.5;pointer-events:none' : ''">
                                <template x-for="red in opcionesRedes" :key="red.id">
                                    <button @click="agregarRed(red)" x-show="!redesSeleccionadas.find(r => r.id === red.id)" class="ed-red">
                                        <span x-html="red.svg"></span><span x-text="red.id === 'x' ? 'X' : red.nombre"></span>
                                    </button>
                                </template>
                            </div>
                            <p class="dt-sub" style="font-size:12px;margin:0 0 10px" x-show="redesSeleccionadas.length > 0">Escribe solo tu nombre de usuario (ej. carlos_123); el enlace se crea solo.</p>
                            <div style="display:flex;flex-direction:column;gap:8px">
                                <template x-for="(red, index) in redesSeleccionadas" :key="index">
                                    <div class="dt-inline">
                                        <span class="ed-ico-red" x-html="red.svg"></span>
                                        <input type="text" x-model="red.usuario" :placeholder="'Tu usuario de ' + red.nombre" :aria-label="'Usuario de ' + red.nombre">
                                        <button @click="quitarRed(index)" style="border:0;background:none;padding:0 12px;color:var(--danger);font-size:18px;cursor:pointer" aria-label="Quitar red">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- PASO 7: billeteras --}}
                        <div x-show="paso === 7" x-cloak>
                            <span class="dt-label">Billeteras digitales (opcional)</span>
                            <div style="display:flex;flex-direction:column;gap:10px">
                                <div class="dt-inline"><span style="color:#7a3fd6">Zelle</span><input type="text" x-model="zelle" placeholder="Correo o teléfono" aria-label="Zelle"></div>
                                <div class="dt-inline"><span style="color:#1a9c3e">CashApp</span><input type="text" x-model="cashapp" placeholder="$Cashtag" aria-label="CashApp"></div>
                                <div class="dt-inline"><span style="color:#008cff">Venmo</span><input type="text" x-model="venmo" placeholder="@usuario" aria-label="Venmo"></div>
                            </div>
                        </div>

                        {{-- PASO 8: diseño --}}
                        <div x-show="paso === 8" x-cloak>
                            <span class="dt-label">Estilo</span>
                            <div class="dt-styles" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px;margin-bottom:20px">
                                <template x-for="e in estilosEditor" :key="e.id">
                                    <div class="dt-tile" :class="presetActivo() === e.id ? 'sel' : ''">
                                        <button class="dt-thumb" @click="aplicarPreset(e.id)" :aria-label="'Estilo ' + e.nombre">
                                            <x-mini-perfil var="({ tema: presets[e.id].tema_base, color: presets[e.id].color_primario, forma: presets[e.id].forma_botones, estructura: presets[e.id].layout_estructura, foto: fotoPreview, inicial: (nombre || '?').charAt(0).toUpperCase() })" :solo="true" />
                                            <span class="dt-check" x-show="presetActivo() === e.id"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
                                        </button>
                                        <b style="font-size:12.5px" x-text="e.nombre"></b>
                                    </div>
                                </template>
                            </div>

                            <div style="display:flex;flex-wrap:wrap;gap:18px;align-items:flex-end">
                                <div x-show="config.layout_estructura !== 'feria'">
                                    <span class="dt-label">Alineación</span>
                                    <div class="dt-seg">
                                        <button :class="config.alineacion === 'centro' ? 'on' : ''" @click="config.alineacion = 'centro'; alineacion = 'centro';">Centro</button>
                                        <button :class="config.alineacion === 'izquierda' ? 'on' : ''" @click="config.alineacion = 'izquierda'; alineacion = 'izquierda';">Izquierda</button>
                                    </div>
                                </div>
                                <div>
                                    <span class="dt-label">Botones</span>
                                    <div class="dt-seg">
                                        <button :class="config.forma_botones === 'rounded-none' ? 'on' : ''" @click="config.forma_botones = 'rounded-none'">Rectos</button>
                                        <button :class="config.forma_botones === 'rounded-xl' ? 'on' : ''" @click="config.forma_botones = 'rounded-xl'">Suaves</button>
                                        <button :class="config.forma_botones === 'rounded-full' ? 'on' : ''" @click="config.forma_botones = 'rounded-full'">Píldora</button>
                                    </div>
                                </div>
                                <div>
                                    <span class="dt-label">Color de acento</span>
                                    <label class="dt-inline" style="height:38px;padding-right:6px;cursor:pointer">
                                        <span x-text="config.color_primario"></span>
                                        <input type="color" x-model="config.color_primario" style="width:30px;height:26px;padding:0;border:0;background:none;cursor:pointer" aria-label="Color de acento">
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- PASO 9: nombre del perfil --}}
                        <div x-show="paso === 9" x-cloak>
                            <label class="dt-label" for="ed-nperfil">Nombre de este perfil</label>
                            <p class="dt-sub" style="margin:0 0 12px;font-size:12.5px">Solo lo ves tú en el panel (ej. «Perfil ejecutivo», «Fin de semana»).</p>
                            <input id="ed-nperfil" type="text" x-model="nombrePerfil" placeholder="Mi Tarjeta Principal" class="dt-input">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botones de navegación --}}
            <footer class="ed-foot">
                <button x-show="paso > (modoActual === 'editar' ? 2 : 1)" @click="paso--" class="dt-btn" style="height:40px">Atrás</button>
                <span x-show="!(paso > (modoActual === 'editar' ? 2 : 1))"></span>
                <div style="display:flex;gap:8px">
                    <button @click="modalPreviewMovil = true" class="dt-btn md:hidden" style="height:40px">Ver tarjeta</button>
                    <button x-show="paso === 1 && modoActual === 'crear'" @click="avanzar(2)" :disabled="!nickValidado" class="dt-btn pri" style="height:40px">Continuar</button>
                    <button x-show="paso >= 2 && paso < totalPasos" @click="avanzar(paso === 2 && tipoPerfil === 'empresa' ? 4 : paso + 1)" class="dt-btn pri" style="height:40px">Continuar</button>
                    <button x-show="paso === totalPasos" @click="guardarPerfilCompleto" :disabled="guardando" class="dt-btn pri" style="height:40px">
                        <span x-show="!guardando">Finalizar y guardar</span><span x-show="guardando">Guardando…</span>
                    </button>
                </div>
            </footer>
        </section>

        {{-- Vista previa del perfil público (mantiene el estilo propio del perfil) --}}
        <section class="ed-preview" :class="modalPreviewMovil ? 'abierta' : ''" aria-label="Vista previa">
            <span class="ed-preview-label">Vista previa</span>
            <button x-show="modalPreviewMovil" @click="modalPreviewMovil = false" class="dt-ico" style="position:absolute;top:14px;right:16px;width:36px;height:36px;border-radius:50%" aria-label="Cerrar vista previa">&times;</button>
        <div class="phone-mockup flex flex-col items-center pt-12 pb-2 relative scale-[0.95] md:scale-100">
            <div class="phone-notch"></div>
            
            <div class="w-full h-full overflow-y-auto hide-scrollbar pb-6 relative" 
                 :style="config.tema_base === 'crystal' ? 'background-image: linear-gradient(to bottom, rgba(9, 9, 11, 0.90), rgba(9, 9, 11, 0.98)), url(\'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop\'); background-size: cover;' : 'background-color: ' + config.color_fondo + ';'">
                
                <!-- Inyección de variables CSS para el Live Preview -->
                <div class="flex flex-col transition-all duration-500 min-h-[90%]" :class="`tpl-${config.tema_base || plantilla}`" :style="`--color-primario: ${config.color_primario};`">
                    
                    <!-- ESTRUCTURA FERIA -->
                    <div x-show="config.layout_estructura === 'feria'" style="display: none;" class="w-full flex flex-col items-center">
                        <div class="w-full aspect-square mb-6 overflow-hidden border-2 border-gray-800 shadow-lg" :class="config.forma_botones">
                            <img x-show="fotoPreview" :src="fotoPreview" class="w-full h-full object-cover">
                            <div x-show="!fotoPreview" class="w-full h-full bg-gray-900 flex items-center justify-center text-4xl font-black text-gray-700" x-text="nombre ? nombre.charAt(0).toUpperCase() : '?'"></div>
                        </div>
                        <div class="text-center w-full mb-4 px-4">
                            <h2 class="text-[22px] font-black tracking-tight leading-tight" x-text="nombre || 'Tu Nombre'"></h2 >
                            <h3 x-show="tipoPerfil !== 'empresa'" class="accent-text text-[10px] font-bold mt-1 uppercase tracking-widest" x-text="profesion || 'Tu Profesión'"></h3>
                        </div>
                        <div class="h-14 w-[calc(100%-2rem)] mx-4 bg-white text-black font-black text-sm flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.2)] mb-6" :class="config.forma_botones">
                            GUARDAR CONTACTO
                        </div>
                    </div>

                    <!-- ESTRUCTURA CLÁSICA -->
                    <div x-show="config.layout_estructura !== 'feria'" class="w-full">
                        <div :class="config.alineacion === 'izquierda' ? 'flex flex-row items-center gap-4 mb-4 text-left w-full px-4 pt-4' : 'flex flex-col items-center text-center w-full px-4 pt-4'">
                            <div class="avatar-ring bg-gradient-to-br from-gray-700 to-gray-900 border-4 flex items-center justify-center shadow-lg overflow-hidden flex-shrink-0" 
                                 :class="[tipoPerfil === 'empresa' ? 'rounded-2xl' : 'rounded-full', config.alineacion === 'izquierda' ? 'w-20 h-20 mb-0' : 'w-28 h-28 mb-4']">
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
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="(img, index) in galeriaPreviews" :key="index">
                                <div class="relative w-full aspect-square overflow-hidden border border-gray-800" :class="config.forma_botones">
                                    <img :src="img" class="w-full h-full object-cover">
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="w-full px-4 flex gap-2 mb-4">
                        <div x-show="telefono" class="h-10 bg-gray-800/50 border border-gray-700 w-1/2 flex items-center justify-center text-xs text-white font-bold gap-2 cursor-pointer hover:bg-gray-700" :class="config.forma_botones">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg> SMS
                        </div>
                        <div x-show="telefono" class="h-10 bg-[#25D366]/20 border border-[#25D366]/40 w-1/2 flex items-center justify-center text-xs text-[#4ade80] font-bold gap-2 cursor-pointer" :class="config.forma_botones">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg> WhatsApp
                        </div>
                    </div>

                    <!-- TAMAÑO DE BOTONES DE REDES REDUCIDO A h-10 -->
                    <div class="w-full px-4 grid grid-cols-2 gap-2 mb-4" x-show="redesSeleccionadas.length > 0">
                        <template x-for="red in redesSeleccionadas" :key="red.id">
                            <div class="h-10 flex items-center justify-center text-[10px] font-bold gap-2 cursor-pointer bg-gray-900 text-white border border-gray-800" :class="config.forma_botones">
                                <div class="w-4 h-4" x-html="red.svg"></div> <span x-text="red.nombre" class="uppercase tracking-wider"></span>
                            </div>
                        </template>
                    </div>

                    <div class="w-full px-4 space-y-2 mb-6">
                        <div x-show="zelle" class="w-full h-11 btn-zelle flex items-center justify-center text-xs font-bold cursor-pointer gap-2" :class="config.forma_botones">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg> Pagar con Zelle
                        </div>
                        <div x-show="cashapp" class="w-full h-11 btn-cashapp flex items-center justify-center text-xs font-bold cursor-pointer" :class="config.forma_botones">Pagar con CashApp</div>
                        <div x-show="venmo" class="w-full h-11 btn-venmo flex items-center justify-center text-xs font-bold cursor-pointer" :class="config.forma_botones">Pagar con Venmo</div>
                    </div>

                    <div x-show="config.layout_estructura !== 'feria'" class="h-11 w-[calc(100%-2rem)] mx-4 bg-white text-black font-bold text-xs flex items-center justify-center shadow-[0_0_15px_rgba(255,255,255,0.2)] cursor-pointer hover:bg-gray-200 mt-auto gap-2" :class="config.forma_botones">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Guardar Contacto
                    </div>
                </div>
            </div>
        </div>
        </section>
    </div>

    {{-- Recorte de la foto --}}
    <div x-show="cropperModalAbierto" x-cloak style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;display:grid;place-items:center;padding:16px">
        <div class="dt-panel" style="width:100%;max-width:420px;display:flex;flex-direction:column;align-items:center">
            <h2 style="margin:0 0 4px;font-size:16px">Ajusta tu foto de perfil</h2>
            <p class="dt-sub" style="margin:0 0 16px;text-align:center;font-size:12.5px">Acerca, aleja o mueve la foto hasta que quede como quieres.</p>
            <div style="width:100%;max-width:300px;aspect-ratio:1;background:#000;border-radius:10px;overflow:hidden;margin-bottom:12px">
                <img id="imagen-cropper" :src="imgParaRecortar" alt="" style="max-width:100%;display:block">
            </div>
            <div style="display:flex;gap:8px;margin-bottom:16px">
                <button type="button" @click="window.cropperInstancia.zoom(-0.1)" class="dt-btn" style="width:40px;padding:0;font-size:18px" aria-label="Alejar">−</button>
                <button type="button" @click="window.cropperInstancia.zoom(0.1)" class="dt-btn" style="width:40px;padding:0;font-size:18px" aria-label="Acercar">+</button>
            </div>
            <div style="display:flex;gap:10px;width:100%">
                <button type="button" @click="cancelarRecorte" class="dt-btn" style="flex:1;height:40px">Cancelar</button>
                <button type="button" @click="aplicarRecorte" class="dt-btn pri" style="flex:1;height:40px">Aplicar foto</button>
            </div>
        </div>
    </div>

    <!-- MODAL GIGANTE DE ÉXITO -->
    <div x-show="modalExitoAbierto" class="fixed inset-0 bg-black/98 z-[9999] flex items-center justify-center p-6 backdrop-blur-xl" style="display: none;">
        <div class="w-full max-w-lg text-center flex flex-col items-center">
            <div class="w-24 h-24 bg-green-500/20 rounded-full flex items-center justify-center mb-8 border-2 border-green-500 shadow-[0_0_50px_rgba(0,214,50,0.3)]">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h1 class="text-4xl font-black text-white mb-4 tracking-tight" x-text="modoActual === 'editar' ? '¡Cambios Guardados!' : '¡Perfil Creado con Éxito!'"></h1>
            <p class="text-gray-300 text-lg mb-2" x-show="modoActual === 'crear'">Tu identidad digital ya está guardada y lista.</p>
            <p class="text-blue-400 font-bold mb-10 text-xl border border-blue-500/30 bg-blue-600/10 py-4 px-6 rounded-2xl inline-block shadow-lg" x-show="modoActual === 'crear'">
                Paso final: Para activar tu tarjeta inteligente, simplemente acercala ahora a la parte superior de tu celular.
            </p>
            <p class="text-gray-400 mb-10 text-lg" x-show="modoActual === 'editar'">Tus actualizaciones ya se reflejan en tu perfil y tarjeta vinculada.</p>
            <button @click="irAlDashboard" class="bg-white text-black font-black py-4 px-10 rounded-2xl transition-all shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:scale-105">
                Volver a Mis Perfiles
            </button>
        </div>
    </div>

    <!-- MODALES EXTRAS (Zelle y Lightbox) -->
    <div x-show="modalZelleAbierto" x-transition.opacity class="fixed inset-0 bg-black/90 z-[9999] flex flex-col justify-end backdrop-blur-sm" style="display: none;">
        <div class="bg-[#121214] w-full rounded-t-2xl p-5 border-t border-gray-800 relative" @click.away="modalZelleAbierto = false">
            <button @click="modalZelleAbierto = false" class="absolute top-4 right-4 text-gray-500 hover:text-white text-2xl">&times;</button>
            <div class="flex items-center justify-center gap-2 text-white font-bold text-sm mb-4">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg> Zelle Transfer
            </div>
            <div class="bg-[#25d366]/10 border border-[#25d366]/40 rounded-xl p-3 mb-4 text-center">
                <span class="text-[#f59e0b] font-black text-sm block mb-1" x-text="zelle"></span>
                <span class="text-[#25d366] text-[10px] font-bold">✓ Copiado al portapapeles</span>
            </div>
        </div>
    </div>

    <div x-show="lightboxAbierto" x-transition.opacity class="fixed inset-0 bg-black/95 z-[9999] flex items-center justify-center p-4 backdrop-blur-md" style="display: none;" @click="lightboxAbierto = false">
        <button class="absolute top-4 right-4 text-white text-4xl font-light" @click="lightboxAbierto = false">&times;</button>
        <img :src="imgActivaLightbox" class="w-full rounded-xl object-contain max-h-full">
    </div>

    <script>
        function perfilDigital() {
            return {
                id_perfil_editar: null, 
                modoActual: 'crear', 
                paso: 1, totalPasos: 9,
                alineacion: 'centro', plantilla: 'crystal', tipoPerfil: 'corporativo',
                
                nickname: '', nickValidado: false, nickError: '', 
                nombre: '', profesion: '', bio: '',
                telefono: '', correo: '', direccion: '', 
                zelle: '', cashapp: '', venmo: '',
                
                fotoPreview: null, galeriaPreviews: [], toastError: '',
                validandoNick: false, guardando: false, generandoIA: false, 
                cropperModalAbierto: false, modalExitoAbierto: false, imgParaRecortar: '',
                modalZelleAbierto: false, lightboxAbierto: false, imgActivaLightbox: '',
                
                modalPreviewMovil: false, tarjetaVinculada: false, 
                
                nombrePerfil: 'Mi Tarjeta Principal',
                config: { tema_base: 'crystal', color_fondo: 'transparent', color_primario: '#3b82f6', alineacion: 'centro', forma_botones: 'rounded-2xl', layout_estructura: 'clasica' },
                presets: { luxury: { tema_base: 'luxury', color_fondo: '#050505', color_primario: '#D4AF37', alineacion: 'centro', forma_botones: 'rounded-none', layout_estructura: 'clasica' }, sport: { tema_base: 'sport', color_fondo: '#000000', color_primario: '#ccff00', alineacion: 'izquierda', forma_botones: '-skew-x-12', layout_estructura: 'clasica' }, corporativo: { tema_base: 'corporativo', color_fondo: '#f8fafc', color_primario: '#0f172a', alineacion: 'izquierda', forma_botones: 'rounded-md', layout_estructura: 'clasica' }, crystal: { tema_base: 'crystal', color_fondo: 'transparent', color_primario: '#3b82f6', alineacion: 'centro', forma_botones: 'rounded-2xl', layout_estructura: 'clasica' }, minimalista: { tema_base: 'minimalista', color_fondo: '#ffffff', color_primario: '#000000', alineacion: 'centro', forma_botones: 'rounded-full', layout_estructura: 'clasica' }, feria: { tema_base: 'matte', color_fondo: '#09090b', color_primario: '#ff3b30', alineacion: 'centro', forma_botones: 'rounded-xl', layout_estructura: 'feria' } },
                estilosEditor: [ { id: 'crystal', nombre: 'Crystal' }, { id: 'feria', nombre: 'Modo Feria' }, { id: 'luxury', nombre: 'Luxury' }, { id: 'sport', nombre: 'Sport' }, { id: 'corporativo', nombre: 'Corporativo' }, { id: 'minimalista', nombre: 'Minimal' } ],
                // Estilo marcado en las fichas del paso 8 (el modo Feria manda sobre el tema)
                presetActivo() { return this.config.layout_estructura === 'feria' ? 'feria' : this.config.tema_base; },

                aplicarPreset(nombre) { 
                    this.config = { ...this.presets[nombre] }; 
                    this.plantilla = this.config.tema_base; 
                    this.alineacion = this.config.alineacion; 
                },
                
                opcionesRedes: [
                    { id: 'ig', nombre: 'Instagram', claseCSS: 'btn-ig', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path></svg>' },
                    { id: 'fb', nombre: 'Facebook', claseCSS: 'btn-fb', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>' },
                    { id: 'tk', nombre: 'TikTok', claseCSS: 'btn-tk', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>' },
                    { id: 'in', nombre: 'LinkedIn', claseCSS: 'btn-in', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>' },
                    { id: 'x', nombre: 'X (Twitter)', claseCSS: 'btn-tk', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733 -16z"></path><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"></path></svg>' },
                    { id: 'yt', nombre: 'YouTube', claseCSS: 'btn-ig', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33 2.78 2.78 0 0 0 1.94 2c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.33 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>' }
                ],
                redesSeleccionadas: [],

                titulos: {
                    1: 'Tu Nombre Digital', 2: 'Identidad Visual', 3: 'Tu Especialidad',
                    4: 'Biografía', 5: 'Portafolio Visual', 6: 'Información y Redes',
                    7: 'Módulo de Pagos', 8: 'Diseño Personalizado', 9: 'Guardar Perfil'
                },
                subtitulos: {
                    1: 'Crea tu usuario único.', 2: 'Define tu estilo y sube tu foto de perfil.', 3: 'Añade tu cargo principal.',
                    4: 'Describe brevemente lo que haces.', 5: 'Sube fotos para crear una cuadrícula visual.',
                    6: 'Elige cómo prefieres que te contacten.', 7: 'Agiliza tus cobros rápidamente.',
                    8: 'Mezcla la distribución y los colores a tu gusto.', 9: 'Bautiza tu configuración.'
                },

                get progreso() { return Math.round((this.paso / this.totalPasos) * 100); },

                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    this.modoActual = 'crear';
                    
                    @if(isset($perfil) && $perfil)
                        this.id_perfil_editar = {{ $perfil->id }};
                        this.nickname = @json($perfil->nickname) || '';
                        this.nombre = @json($perfil->nombre) || '';
                        this.profesion = @json($perfil->profesion) || '';
                        this.bio = @json($perfil->bio) || '';
                        this.telefono = @json($perfil->telefono) || '';
                        this.correo = @json($perfil->correo_contacto) || '';
                        this.direccion = @json($perfil->direccion) || '';
                        this.zelle = @json($perfil->zelle) || '';
                        this.cashapp = @json($perfil->cashapp) || '';
                        this.venmo = @json($perfil->venmo) || '';
                        this.tipoPerfil = @json($perfil->tipo_perfil) || 'corporativo';
                        this.nombrePerfil = @json($perfil->nombre_perfil) || 'Mi Tarjeta Principal';
                        this.fotoPreview = @json($perfil->foto_perfil);
                        
                        let r = @json($perfil->redes_sociales);
                        this.redesSeleccionadas = typeof r === 'string' ? JSON.parse(r||'[]') : (r||[]);

                        let g = @json($perfil->galeria);
                        this.galeriaPreviews = typeof g === 'string' ? JSON.parse(g||'[]') : (g||[]);

                        let c = @json($perfil->design_config);
                        let pConf = typeof c === 'string' ? JSON.parse(c||'null') : c;
                        if(pConf) {
                            this.config = pConf;
                            this.plantilla = pConf.tema_base || 'crystal';
                            this.alineacion = pConf.alineacion || 'centro';
                        }
                        this.modoActual = 'editar';
                        this.tarjetaVinculada = true;
                    @endif

                    if (urlParams.has('step')) {
                        this.paso = parseInt(urlParams.get('step'));
                        this.modoActual = 'editar';
                    } else if (urlParams.has('id') || urlParams.has('editar') || urlParams.has('modo')) {
                        this.modoActual = 'editar';
                    }

                    // Magia: Si es edición, el usuario no pasa por el Paso 1
                    if (this.modoActual === 'editar' && !urlParams.has('step')) {
                        this.paso = 2;
                    }
                },

                async validarNickname() {
                    if (this.nickname.trim() === '') { 
                        this.nickError = "Debes ingresar un usuario para validar."; 
                        return; 
                    }
                    this.validandoNick = true;
                    this.nickError = '';
                    try {
                        let res = await fetch('/perfil/validar-nickname?nickname=' + this.nickname.toLowerCase().replace(/\s+/g, ''));
                        let data = await res.json();
                        if (data.disponible) {
                            this.nickValidado = true; // Desbloquea botón Continuar
                        } else {
                            this.nickValidado = false;
                            this.nickError = "Lo sentimos, el usuario '" + this.nickname + "' ya está en uso.";
                        }
                    } catch (e) {
                        this.nickError = "Error al validar el enlace.";
                    }
                    this.validandoNick = false;
                },

                async mejorarConIA() {
                    if (!this.profesion && !this.bio) { 
                        this.toastError = "Escribe al menos tu profesión en el paso anterior para que la IA sepa qué redactar."; 
                        return; 
                    }
                    this.generandoIA = true;
                    try {
                        let response = await fetch('/api/ia/generar-bio', { 
                            method: 'POST', 
                            headers: { 
                                'Content-Type': 'application/json', 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                            }, 
                            body: JSON.stringify({ profesion: this.profesion, bio_actual: this.bio }) 
                        });
                        let data = await response.json();
                        if (data.status === 'success') { 
                            this.bio = data.bio; 
                        } else { 
                            this.toastError = data.message || "La IA no pudo responder, intenta de nuevo."; 
                        }
                    } catch (e) { 
                        this.toastError = "Error conectando con el motor de IA."; 
                    }
                    this.generandoIA = false;
                },

                avanzar(siguientePaso) {
                    this.toastError = '';
                    this.paso = siguientePaso;
                },

                agregarRed(red) { 
                    if (this.redesSeleccionadas.length < 4) {
                        this.redesSeleccionadas.push({ ...red, usuario: '' }); 
                    }
                },
                
                quitarRed(index) { 
                    this.redesSeleccionadas.splice(index, 1); 
                },
                
                abrirLightboxMockup(imgSrc) { 
                    this.imgActivaLightbox = imgSrc; 
                    this.lightboxAbierto = true; 
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
                                aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 1, cropBoxMovable: false, cropBoxResizable: false, zoomable: true, guides: false, background: false
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

                procesarImagenesGaleria(event) {
                    const archivos = event.target.files;
                    if (!archivos) return;
                    let disp = 4 - this.galeriaPreviews.length;
                    for (let i = 0; i < Math.min(archivos.length, disp); i++) {
                        let reader = new FileReader();
                        reader.onload = (e) => { this.galeriaPreviews.push(e.target.result); };
                        reader.readAsDataURL(archivos[i]);
                    }
                },
                
                eliminarImagenGaleria(index) { 
                    this.galeriaPreviews.splice(index, 1); 
                },

                async guardarPerfilCompleto() {
                    if(this.modoActual === 'crear' && (!this.nombre || !this.nickname)) {
                        this.toastError = "Por favor, vuelve al inicio y llena tu Nombre y Usuario.";
                        return;
                    }
                    if (this.modoActual === 'editar' && !this.nombre) {
                        this.toastError = "El nombre es obligatorio.";
                        return;
                    }

                    this.guardando = true;
                    // La Forja: la unidad DT-9 ensambla el perfil mientras el servidor lo guarda
                    const forja = window.Forja;
                    forja?.iniciar({ inicial: this.nombre || this.nickname, color: this.config.color_primario, modo: this.modoActual });

                    let formData = new FormData();
                    formData.append('perfil_id', this.id_perfil_editar || 'null');
                    formData.append('nickname', this.nickname.toLowerCase().replace(/\s+/g, ''));
                    formData.append('nombre', this.nombre);
                    formData.append('tipo_perfil', this.tipoPerfil);
                    formData.append('plantilla', this.plantilla); 
                    formData.append('alineacion', this.alineacion); 
                    formData.append('profesion', this.profesion);
                    formData.append('bio', this.bio);
                    formData.append('telefono', this.telefono);
                    formData.append('correo_contacto', this.correo);
                    formData.append('direccion', this.direccion);
                    formData.append('zelle', this.zelle);
                    formData.append('cashapp', this.cashapp);
                    formData.append('venmo', this.venmo);
                    formData.append('foto_perfil', this.fotoPreview || '');
                    formData.append('redes_sociales', JSON.stringify(this.redesSeleccionadas));
                    formData.append('galeria', JSON.stringify(this.galeriaPreviews));
                    
                    formData.append('nombre_perfil', this.nombrePerfil);
                    formData.append('design_config', JSON.stringify(this.config));

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
                            forja ? forja.terminar({ ok: true, nickname: this.nickname.toLowerCase().replace(/\s+/g, '') }) : (this.modalExitoAbierto = true);
                        } else {
                            this.toastError = resultado.message || "Error al procesar los datos.";
                            forja?.terminar({ ok: false, mensaje: this.toastError });
                        }
                    } catch (error) {
                        this.toastError = 'Error de conexión con el servidor.';
                        forja?.terminar({ ok: false, mensaje: this.toastError });
                    }
                    this.guardando = false;
                },

                irAlDashboard() { 
                    window.location.href = '/dashboard/perfiles'; 
                }
            }
        }
    </script>

    @include('partials.forja')
</body>
</html>
