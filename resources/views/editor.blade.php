<!DOCTYPE html>
<html lang="es" {!! \App\Support\TemaPanel::atributos(Auth::user()) !!}>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor de perfil · DaUnToque</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.panel-estilos')
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ \App\Support\Tipografias::urlGoogle() }}">

    @vite('resources/css/panel.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <style>
        /* Editor: formulario a la izquierda con el estilo del panel, vista previa del perfil a la derecha */
        .ed-app { position:relative; z-index:1; display:flex; height:100vh; overflow:hidden; }
        .ed-form { width:50%; min-width:0; display:flex; flex-direction:column; }
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
        .ed-step.done i { background:var(--primary); border-color:var(--primary); color:var(--on-pri); }
        .ed-step.done:hover { background:var(--surface); }
        .ed-main { flex:1; min-width:0; padding:28px 28px 120px; max-width:600px; }
        .ed-bar { height:5px; border-radius:9px; background:var(--surface); overflow:hidden; margin:14px 0 22px; }
        .ed-bar span { display:block; height:100%; background:var(--primary); transition:width .4s; }
        .ed-foot { flex:none; border-top:1px solid var(--border); padding:12px 24px; display:flex; justify-content:space-between; gap:10px; background:var(--bg); }
        .ed-opt { height:38px; border:1px solid var(--border); border-radius:7px; background:var(--card); color:var(--muted); font-size:13px; font-weight:600; cursor:pointer; }
        .ed-opt.on { border-color:var(--primary); background:var(--primary-soft); color:var(--primary); }
        .ed-drop { border:1.5px dashed var(--border); border-radius:10px; background:var(--surface); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; padding:18px; color:var(--muted); font-size:13px; cursor:pointer; position:relative; text-align:center; }
        .ed-drop:hover { border-color:var(--primary); color:var(--text); }
        /* Redes: cuadrícula de botones del mismo tamaño (3 columnas) */
        .ed-redes { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:8px; margin-bottom:12px; }
        .ed-redes.lleno { opacity:.5; pointer-events:none; }
        .ed-red { width:100%; min-height:64px; justify-content:center; border:1px solid var(--border); border-radius:8px; background:var(--card); padding:8px 4px; display:flex; flex-direction:column; align-items:center; gap:4px; font-size:10px; font-weight:600; color:var(--muted); cursor:pointer; text-transform:uppercase; letter-spacing:.04em; }
        .ed-red:hover { border-color:var(--primary); color:var(--text); }
        .ed-red svg, .ed-ico-red svg { width:18px; height:18px; }
        /* Portafolio: miniaturas de tamaño fijo, cada una con el nombre del producto o servicio */
        .ed-gal { display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:12px; }
        .ed-gal-item { display:flex; flex-direction:column; gap:6px; min-width:0; }
        .ed-gal-foto { position:relative; aspect-ratio:1; border-radius:8px; overflow:hidden; border:1px solid var(--border); background:var(--surface); }
        .ed-gal-foto img { display:block; width:100%; height:100%; object-fit:cover; }
        .ed-gal-foto .dt-ico { position:absolute; top:6px; right:6px; width:26px; height:26px; border-radius:50%; }
        .ed-gal-item input { width:100%; height:34px; padding:0 10px; border:1px solid var(--border); border-radius:7px; background:var(--card); color:var(--text); font:inherit; font-size:12.5px; }
        .ed-gal-item input:focus { outline:none; border-color:var(--primary); }
        .ed-paises { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:20px; }
        .ed-pais { display:flex; align-items:center; gap:10px; padding:12px; border:1px solid var(--border); border-radius:10px; background:var(--card); color:var(--muted); font-size:13.5px; font-weight:600; cursor:pointer; text-align:left; }
        .ed-pais b { width:30px; height:30px; flex:none; border-radius:8px; display:grid; place-items:center; background:var(--surface); font-size:11px; letter-spacing:.04em; color:var(--text); }
        .ed-pais.on { border-color:var(--primary); background:var(--primary-soft); color:var(--text); box-shadow:0 0 0 3px var(--primary-soft); }
        .ed-pais.on b { background:var(--primary); color:var(--on-pri); }
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
        /* Teléfono de la vista previa: tamaño real en escritorio, miniatura flotante en el celular */
        .ed-telefono { width:320px; height:650px; border:10px solid #18181b; border-radius:44px; overflow:hidden; position:relative; background:#000; flex:none;
                       box-shadow:0 30px 60px -20px rgba(0,0,0,.45), inset 0 0 0 1px rgba(255,255,255,.06); transform-origin:center; }
        .ed-notch { position:absolute; top:0; left:50%; transform:translateX(-50%); width:110px; height:22px; background:#18181b; border-radius:0 0 14px 14px; z-index:30; }
        .ed-pantalla { position:absolute; inset:0; overflow-y:auto; scrollbar-width:none; }
        .ed-pantalla::-webkit-scrollbar { display:none; }
        .ed-pantalla .pp-wrap { padding-top:30px; }
        .ed-tocar, .ed-cerrar { display:none; }
        @media (max-height: 780px) and (min-width: 769px) { .ed-telefono { transform:scale(.86); } }
        @media (max-height: 680px) and (min-width: 769px) { .ed-telefono { transform:scale(.74); } }
        @media (max-width: 768px) {
            .ed-preview { display:block; position:fixed; right:10px; bottom:76px; width:96px; height:196px; z-index:60; border:0; background:transparent; overflow:visible; }
            .ed-preview .ed-preview-label { display:none; }
            .ed-preview .ed-telefono { position:absolute; top:0; left:0; transform:scale(.3); transform-origin:top left; cursor:zoom-in; box-shadow:0 20px 40px rgba(0,0,0,.35); }
            .ed-preview .ed-pantalla { pointer-events:none; }
            .ed-tocar { display:block; position:absolute; left:50%; bottom:-4px; transform:translate(-50%, 100%); white-space:nowrap; font-size:10.5px; font-weight:600; color:var(--text); background:var(--card); border:1px solid var(--border); border-radius:999px; padding:2px 8px; }
            .ed-preview.abierta { position:fixed; inset:0; width:auto; height:auto; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px; background:var(--bg); padding:16px; }
            .ed-preview.abierta .ed-telefono { position:relative; transform:scale(.9); transform-origin:center; cursor:default; }
            .ed-preview.abierta .ed-pantalla { pointer-events:auto; }
            .ed-preview.abierta .ed-cerrar { display:inline-flex; position:absolute; top:12px; left:50%; transform:translateX(-50%); z-index:5; }
            .ed-foot { padding-right:120px; }
        }
        @media (max-width: 768px) and (max-height: 760px) { .ed-preview.abierta .ed-telefono { transform:scale(.8); } }
        @media (max-width: 768px) and (max-height: 660px) { .ed-preview.abierta .ed-telefono { transform:scale(.68); } }
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
                <span class="dt-chip" x-text="modoActual === 'editar' ? 'Editando perfil' : 'Creando perfil nuevo'"></span>
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
                                <div class="dt-inline" style="flex:1"><span>{{ \App\Models\Profile::prefijoPublico() }}</span><input id="ed-nick" type="text" x-model="nickname" @input="nickValidado = false; nickError = ''" placeholder="carlos" style="text-transform:lowercase"></div>
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
                                <button @click="mejorarConIA" :disabled="generandoIA" class="dt-btn" style="border-color:rgba(139,92,246,.5);background:linear-gradient(90deg,#7c3aed,#2563eb);color:#fff;box-shadow:0 0 18px rgba(124,58,237,.35)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/></svg>
                                    <span x-show="!generandoIA">Mejorar con IA</span><span x-show="generandoIA">Redactando…</span>
                                </button>
                            </div>

                            {{-- Botón "Agendar cita" de la app Agenda --}}
                            <div class="dt-panel" style="margin-top:20px;padding:16px">
                                <b style="font-size:14px">📅 Botón "Agendar cita"</b>
                                @if($agendaEstado === 'activa')
                                    <label style="display:flex;gap:10px;align-items:center;margin-top:12px;cursor:pointer">
                                        <input type="checkbox" :checked="config.agenda_boton !== false" @change="config.agenda_boton = $event.target.checked"> Mostrar el botón en este perfil
                                    </label>
                                    <label style="display:flex;gap:10px;align-items:center;margin-top:8px;cursor:pointer" x-show="config.agenda_boton !== false">
                                        <input type="checkbox" :checked="config.agenda_prioridad === true" @change="config.agenda_prioridad = $event.target.checked"> Darle prioridad: ponerlo justo después de la descripción
                                    </label>
                                @elseif($agendaEstado === 'pendiente')
                                    <p class="dt-sub">Tu agenda está activa, pero falta crear un servicio y tu horario para que el botón aparezca.</p>
                                    <a class="dt-btn" href="/dashboard/agenda/ajustes" target="_blank" style="margin-top:10px">Completar la agenda</a>
                                @elseif($agendaEstado === 'basica')
                                    <p class="dt-sub">Tu agenda venció. Renuévala para volver a mostrar el botón.</p>
                                    <a class="dt-btn" href="/dashboard/agenda" target="_blank" style="margin-top:10px">Ver mi agenda</a>
                                @else
                                    <p class="dt-sub">Tus clientes reservan desde tu tarjeta. Gratis el primer año.</p>
                                    <a class="dt-btn pri" href="/dashboard/apps" target="_blank" style="margin-top:10px">Activar la agenda</a>
                                @endif
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
                            <p class="dt-sub" style="font-size:12px;margin:0 0 10px" x-show="galeriaPreviews.length > 0">Escribe debajo de cada foto el nombre del producto o servicio; se verá en tu tarjeta.</p>
                            <div class="ed-gal" x-show="galeriaPreviews.length > 0">
                                <template x-for="(img, index) in galeriaPreviews" :key="index">
                                    <div class="ed-gal-item">
                                        <div class="ed-gal-foto">
                                            <img :src="img.url" alt="">
                                            <button @click="eliminarImagenGaleria(index)" class="dt-ico" aria-label="Quitar imagen">&times;</button>
                                        </div>
                                        <input type="text" x-model="img.titulo" maxlength="60" placeholder="Producto o servicio" :aria-label="'Producto o servicio de la foto ' + (index + 1)">
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
                            <div class="ed-redes" :class="redesSeleccionadas.length >= 4 ? 'lleno' : ''">
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
                            <span class="dt-label">¿Dónde cobras?</span>
                            <div class="ed-paises" role="radiogroup" aria-label="País de tus cobros">
                                <template x-for="(nombrePais, codigo) in mercados" :key="codigo">
                                    <button type="button" class="ed-pais" :class="pais === codigo ? 'on' : ''" role="radio" :aria-checked="pais === codigo" @click="pais = codigo">
                                        <b x-text="codigo"></b><span x-text="nombrePais"></span>
                                    </button>
                                </template>
                            </div>
                            <span class="dt-label">Medios de pago (opcional)</span>
                            <div style="display:flex;flex-direction:column;gap:10px">
                                <template x-for="tipo in mediosPais" :key="tipo">
                                    <div class="dt-inline"><span :style="'color:' + mediosPago[tipo].acento" x-text="mediosPago[tipo].nombre"></span><input type="text" x-model="pagos[tipo]" :placeholder="mediosPago[tipo].placeholder" :inputmode="mediosPago[tipo].inputmode" :aria-label="mediosPago[tipo].nombre"></div>
                                </template>
                            </div>
                            <p class="dt-sub" style="font-size:12px;margin:10px 0 0" x-text="pais === 'CO' ? 'Tus clientes verán el botón y podrán copiar tu número o tu llave para pagarte.' : 'Zelle se copia; Cash App y Venmo abren la app con tu usuario.'"></p>
                        </div>

                        {{-- PASO 8: diseño --}}
                        <div x-show="paso === 8" x-cloak>
                            <span class="dt-label">Temas listos</span>
                            <div class="dt-styles" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px;margin-bottom:22px">
                                <template x-for="c in combos" :key="c.id">
                                    <div class="dt-tile" :class="esCombo(c) ? 'sel' : ''">
                                        <button class="dt-thumb" @click="aplicarCombo(c)" :aria-label="'Tema ' + c.nombre">
                                            <x-mini-perfil var="({ tema: c.tema, color: c.color, forma: c.forma, estructura: c.diseno, foto: fotoPreview, inicial: (nombre || '?').charAt(0).toUpperCase() })" :solo="true" />
                                            <span class="dt-check" x-show="esCombo(c)"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
                                        </button>
                                        <b style="font-size:12.5px" x-text="c.nombre"></b>
                                    </div>
                                </template>
                            </div>

                            <span class="dt-label">Diseño de la tarjeta</span>
                            <div class="dt-styles" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px;margin-bottom:22px">
                                <template x-for="d in disenosEditor" :key="d.id">
                                    <div class="dt-tile" :class="(config.layout_estructura || 'clasica') === d.id ? 'sel' : ''">
                                        <button class="dt-thumb" @click="config.layout_estructura = d.id" :aria-label="'Diseño ' + d.nombre">
                                            <x-mini-perfil var="({ tema: config.tema_base, color: config.color_primario, forma: config.forma_botones, estructura: d.id, foto: fotoPreview, inicial: (nombre || '?').charAt(0).toUpperCase() })" :solo="true" />
                                            <span class="dt-check" x-show="(config.layout_estructura || 'clasica') === d.id"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
                                        </button>
                                        <b style="font-size:12.5px" x-text="d.nombre"></b>
                                    </div>
                                </template>
                            </div>

                            <span class="dt-label">Estilo de color</span>
                            <div class="dt-styles" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px;margin-bottom:20px">
                                <template x-for="e in estilosEditor" :key="e.id">
                                    <div class="dt-tile" :class="config.tema_base === e.id ? 'sel' : ''">
                                        <button class="dt-thumb" @click="aplicarTema(e.id)" :aria-label="'Estilo ' + e.nombre">
                                            <x-mini-perfil var="({ tema: presets[e.id].tema_base, color: presets[e.id].color_primario, forma: presets[e.id].forma_botones, estructura: config.layout_estructura, foto: fotoPreview, inicial: (nombre || '?').charAt(0).toUpperCase() })" :solo="true" />
                                            <span class="dt-check" x-show="config.tema_base === e.id"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
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
                            <span class="dt-label" style="margin-top:22px">Tipografía</span>
                            <div class="dt-fuentes" style="margin-bottom:6px">
                                <button type="button" class="dt-fuente" :class="!config.tipografia ? 'on' : ''" @click="config.tipografia = ''">
                                    <b>Aa</b><span>Según el estilo</span>
                                </button>
                                <template x-for="f in tipografias" :key="f.id">
                                    <button type="button" class="dt-fuente" :class="config.tipografia === f.id ? 'on' : ''" @click="config.tipografia = f.id" :aria-label="'Tipografía ' + f.nombre">
                                        <b :style="'font-family:' + f.titulo">Aa</b><span :style="'font-family:' + f.texto" x-text="f.nombre"></span>
                                    </button>
                                </template>
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
                    <button x-show="paso === 1 && modoActual === 'crear'" @click="avanzar(2)" :disabled="!nickValidado" class="dt-btn pri" style="height:40px">Continuar</button>
                    <button x-show="paso >= 2 && paso < totalPasos" @click="avanzar(paso === 2 && tipoPerfil === 'empresa' ? 4 : paso + 1)" class="dt-btn pri" style="height:40px">Continuar</button>
                    <button x-show="paso === totalPasos" @click="guardarPerfilCompleto" :disabled="guardando" class="dt-btn pri" style="height:40px">
                        <span x-show="!guardando">Finalizar y guardar</span><span x-show="guardando">Guardando…</span>
                    </button>
                </div>
            </footer>
        </section>

        {{-- Vista previa del perfil público (mantiene el estilo propio del perfil) --}}
        <section class="ed-preview" :class="modalPreviewMovil ? 'abierta' : ''" aria-label="Vista previa de tu tarjeta">
            <span class="ed-preview-label">Vista previa</span>
            <button x-show="modalPreviewMovil" x-cloak @click="modalPreviewMovil = false" class="dt-btn ed-cerrar" aria-label="Cerrar vista previa">Volver al editor</button>
            {{-- En el celular se ve en miniatura; al tocarla se amplía para seguir el progreso --}}
            <div class="ed-telefono" @click="if (!modalPreviewMovil && window.matchMedia('(max-width: 768px)').matches) modalPreviewMovil = true">
                <div class="ed-notch"></div>
                <div class="ed-pantalla">
                    @include('partials.perfil-publico')
                </div>
            </div>
            <span class="ed-tocar" x-show="!modalPreviewMovil">Toca para ampliar</span>
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

    <!-- Lightbox -->
    <div x-show="lightboxAbierto" x-transition.opacity class="fixed inset-0 bg-black/95 z-[9999] flex items-center justify-center p-4 backdrop-blur-md" style="display: none;" @click="lightboxAbierto = false">
        <button class="absolute top-4 right-4 text-white text-4xl font-light" @click="lightboxAbierto = false">&times;</button>
        <img :src="imgActivaLightbox" class="w-full rounded-xl object-contain max-h-full">
    </div>

    <script>
        // CO o US según la zona horaria del navegador; si no se reconoce, por la región del idioma (es-CO, en-US)
        function dtPaisDetectado(paises) {
            let zona = '';
            try { zona = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) {}
            if (zona === 'America/Bogota') return 'CO';
            if (/^(America\/(New_York|Chicago|Denver|Los_Angeles|Phoenix|Anchorage|Detroit|Boise|Indiana|Kentucky|North_Dakota|Puerto_Rico)|Pacific\/Honolulu)/.test(zona)) return 'US';
            const region = ((navigator.language || '').split('-')[1] || '').toUpperCase();
            return paises.includes(region) ? region : 'CO';
        }

        function perfilDigital() {
            return {
                id_perfil_editar: null, 
                modoActual: 'crear', 
                paso: 1, totalPasos: 9,
                alineacion: 'centro', plantilla: 'crystal', tipoPerfil: 'corporativo',
                
                nickname: '', nickValidado: false, nickError: '', 
                nombre: '', profesion: '', bio: '',
                telefono: '', correo: '', direccion: '', 
                // País y medios de pago (config/pagos.php): Nequi y Llave Bre-B en CO; Zelle, Cash App y Venmo en US
                mercados: @json($mercados ?? []),
                porPais: @json(config('pagos.por_pais')),
                mediosPago: @json(config('pagos.medios')),
                pais: '', pagos: { nequi: '', llave_breb: '', zelle: '', cashapp: '', venmo: '' },
                get mediosPais() { return this.porPais[this.pais] || []; },
                get listaPagos() {
                    return this.mediosPais.filter(t => String(this.pagos[t] || '').trim()).map(t => ({ type: t, value: String(this.pagos[t]).trim() }));
                },

                fotoPreview: null, galeriaPreviews: [], toastError: '',
                validandoNick: false, guardando: false, generandoIA: false, 
                cropperModalAbierto: false, modalExitoAbierto: false, imgParaRecortar: '',
                lightboxAbierto: false, imgActivaLightbox: '',
                
                modalPreviewMovil: false, tarjetaVinculada: false, 
                
                nombrePerfil: 'Mi Tarjeta Principal',
                config: { tema_base: 'crystal', color_fondo: 'transparent', color_primario: '#3b82f6', alineacion: 'centro', forma_botones: 'rounded-2xl', layout_estructura: 'clasica' },
                presets: { onyx: { tema_base: 'onyx', color_fondo: '#080808', color_primario: '#c8a96a', alineacion: 'centro', forma_botones: 'rounded-xl', layout_estructura: 'premium' }, medianoche: { tema_base: 'medianoche', color_fondo: '#060a14', color_primario: '#60a5fa', alineacion: 'centro', forma_botones: 'rounded-2xl', layout_estructura: 'premium' }, rose: { tema_base: 'rose', color_fondo: '#130b10', color_primario: '#e8a3b8', alineacion: 'centro', forma_botones: 'rounded-full', layout_estructura: 'boutique' }, esmeralda: { tema_base: 'esmeralda', color_fondo: '#05110d', color_primario: '#d4af37', alineacion: 'centro', forma_botones: 'rounded-xl', layout_estructura: 'premium' }, luxury: { tema_base: 'luxury', color_fondo: '#050505', color_primario: '#D4AF37', alineacion: 'centro', forma_botones: 'rounded-none', layout_estructura: 'clasica' }, sport: { tema_base: 'sport', color_fondo: '#000000', color_primario: '#ccff00', alineacion: 'izquierda', forma_botones: '-skew-x-12', layout_estructura: 'clasica' }, corporativo: { tema_base: 'corporativo', color_fondo: '#f8fafc', color_primario: '#0f172a', alineacion: 'izquierda', forma_botones: 'rounded-md', layout_estructura: 'clasica' }, crystal: { tema_base: 'crystal', color_fondo: 'transparent', color_primario: '#3b82f6', alineacion: 'centro', forma_botones: 'rounded-2xl', layout_estructura: 'clasica' }, minimalista: { tema_base: 'minimalista', color_fondo: '#ffffff', color_primario: '#000000', alineacion: 'centro', forma_botones: 'rounded-full', layout_estructura: 'clasica' }, matte: { tema_base: 'matte', color_fondo: '#09090b', color_primario: '#ff3b30', alineacion: 'centro', forma_botones: 'rounded-xl', layout_estructura: 'feria' } },
                disenosEditor: [ { id: 'premium', nombre: 'Premium' }, { id: 'boutique', nombre: 'Boutique' }, { id: 'clasica', nombre: 'Clásico' }, { id: 'moderna', nombre: 'Moderno' }, { id: 'feria', nombre: 'Feria' }, { id: 'enlaces', nombre: 'Enlaces' }, { id: 'ejecutiva', nombre: 'Deportivo' } ],
                estilosEditor: [ { id: 'onyx', nombre: 'Ónix' }, { id: 'medianoche', nombre: 'Medianoche' }, { id: 'rose', nombre: 'Rosé' }, { id: 'esmeralda', nombre: 'Esmeralda' }, { id: 'crystal', nombre: 'Crystal' }, { id: 'matte', nombre: 'Mate' }, { id: 'luxury', nombre: 'Luxury' }, { id: 'sport', nombre: 'Sport' }, { id: 'corporativo', nombre: 'Corporativo' }, { id: 'minimalista', nombre: 'Minimal' } ],
                // Temas listos (config/combos.php): diseño, estilo, color y botones de una vez
                combos: @json(config('combos')),
                // Tipografías del perfil (config/tipografias.php); '' deja la letra del estilo
                tipografias: @json(\App\Support\Tipografias::paraVista()),
                esCombo(c) {
                    return (this.config.layout_estructura || 'clasica') === c.diseno && this.config.tema_base === c.tema
                        && String(this.config.color_primario || '').toLowerCase() === c.color && this.config.forma_botones === c.forma;
                },
                aplicarCombo(c) {
                    this.config = { ...this.config, tema_base: c.tema, color_primario: c.color, forma_botones: c.forma, layout_estructura: c.diseno, alineacion: 'centro' };
                    this.plantilla = c.tema;
                    this.alineacion = 'centro';
                },
                // Cambia colores y botones del estilo sin tocar el diseño elegido
                aplicarTema(id) {
                    const diseno = this.config.layout_estructura || 'clasica';
                    this.aplicarPreset(id);
                    this.config.layout_estructura = diseno;
                },

                aplicarPreset(nombre) { 
                    this.config = { ...this.presets[nombre], tipografia: this.config.tipografia || '', agenda_boton: this.config.agenda_boton, agenda_prioridad: this.config.agenda_prioridad }; 
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

                // Datos de la vista previa: la misma plantilla que el perfil público (partials/perfil-publico)
                get p() {
                    return {
                        nombre: this.nombre, profesion: this.profesion, bio: this.bio, foto: this.fotoPreview,
                        tipo: this.tipoPerfil, alineacion: this.config.alineacion, estructura: this.config.layout_estructura,
                        tema: this.config.tema_base || this.plantilla, color: this.config.color_primario, forma: this.config.forma_botones, tipografia: this.config.tipografia || '',
                        telefono: this.telefono, correo: this.correo, direccion: this.direccion,
                        pagos: this.listaPagos,
                        redes: this.redesSeleccionadas, galeria: this.galeriaPreviews,
                        enlace: '#', vcf: '#', preview: true,
                        agenda: @js($agendaEstado === 'activa') && this.config.agenda_boton !== false ? '#' : null,
                        agenda_prioridad: this.config.agenda_prioridad === true,
                    };
                },

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
                        this.pais = @json($perfil->pais) || '';
                        (@json($perfil->payment_methods) || []).forEach(m => { if (m && m.type in this.pagos) this.pagos[m.type] = m.value || ''; });
                        this.tipoPerfil = @json($perfil->tipo_perfil) || 'corporativo';
                        this.nombrePerfil = @json($perfil->nombre_perfil) || 'Mi Tarjeta Principal';
                        this.fotoPreview = @json($perfil->foto_perfil);
                        
                        let r = @json($perfil->redes_sociales);
                        this.redesSeleccionadas = typeof r === 'string' ? JSON.parse(r||'[]') : (r||[]);

                        this.galeriaPreviews = @json(\App\Support\Galeria::normalizar($perfil->galeria));

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

                    // Perfil nuevo: el país sale de la zona horaria (o del idioma) del navegador
                    if (!this.porPais[this.pais]) this.pais = dtPaisDetectado(Object.keys(this.porPais));

                    // Solo se edita un perfil que llegó cargado; sin él siempre se crea (empezando por el usuario, paso 1)
                    if (this.modoActual === 'editar') {
                        const pasoUrl = parseInt(urlParams.get('step'));
                        // Magia: Si es edición, el usuario no pasa por el Paso 1
                        this.paso = pasoUrl >= 2 && pasoUrl <= this.totalPasos ? pasoUrl : 2;
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
                        this.nickname = this.nickname.toLowerCase().replace(/\s+/g, '');
                        let res = await fetch('/perfil/validar-nickname?nickname=' + encodeURIComponent(this.nickname));
                        let data = await res.json();
                        if (data.disponible) {
                            this.nickValidado = true; // Desbloquea botón Continuar
                        } else {
                            this.nickValidado = false;
                            this.nickError = data.mensaje || "Lo sentimos, el usuario '" + this.nickname + "' ya está en uso.";
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

                // Cada foto se reduce antes de guardarla (partials/reducir-imagen), igual que la de perfil
                async procesarImagenesGaleria(event) {
                    const archivos = Array.from(event.target.files || []);
                    event.target.value = '';
                    for (const archivo of archivos.slice(0, 4 - this.galeriaPreviews.length)) {
                        try {
                            const img = await dtReducirImagen(archivo);
                            if (this.galeriaPreviews.length < 4) this.galeriaPreviews.push({ url: img, titulo: '' });
                        } catch (e) {
                            this.toastError = 'No pudimos leer una de las imágenes. Prueba con otra foto (JPG o PNG).';
                        }
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
                    formData.append('pais', this.pais);
                    formData.append('payment_methods', JSON.stringify(this.listaPagos));
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
                        
                        // Un 413 del servidor web llega como página HTML, no como JSON
                        let resultado = respuesta.status === 413
                            ? { message: 'Las fotos pesan demasiado para guardarlas juntas. Usa imágenes más livianas o sube menos a la vez.' }
                            : await respuesta.json();
                        
                        if(resultado.status === 'success') {
                            // "Ver mi perfil" abre el link maestro de la cuenta (muestra el perfil activo)
                            forja ? forja.terminar({ ok: true, nickname: resultado.nickname || this.nickname.toLowerCase().replace(/\s+/g, '') }) : (this.modalExitoAbierto = true);
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

    @include('partials.reducir-imagen')
    @include('partials.forja')
</body>
</html>
