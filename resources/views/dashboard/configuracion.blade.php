@extends('layouts.app')

@section('contenido')
    <script>
        window.perfilCargado = {!! $datosJsonSeguros !!};
    </script>

    <div x-data="gestorConfiguracion()">
        <div class="dt-head" style="position:sticky;top:-28px;z-index:20;background:var(--bg);padding:12px 0;margin-top:-12px;border-bottom:1px solid var(--border)">
            <div>
                <h1 class="dt-h1">Datos del perfil</h1>
                <p class="dt-sub">Actualiza la información de tu tarjeta digital.</p>
            </div>
            <button @click="guardarPerfil" class="dt-btn pri" :disabled="guardando">
                <span x-show="!guardando">Guardar cambios</span>
                <span x-show="guardando">Guardando…</span>
            </button>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;align-items:start">
            <div style="display:flex;flex-direction:column;gap:18px">
                <section class="dt-panel" style="margin:0">
                    <div class="dt-panel-h"><h2>Identidad</h2></div>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px">
                        <div style="width:72px;height:72px;border-radius:50%;overflow:hidden;background:var(--surface);border:1px solid var(--border);display:grid;place-items:center;flex:none">
                            <img x-show="fotoPreview" :src="fotoPreview" alt="" style="width:100%;height:100%;object-fit:cover">
                            <span x-show="!fotoPreview" style="font-size:26px;font-weight:700;color:var(--muted)" x-text="datos.nombre ? datos.nombre.charAt(0).toUpperCase() : '?'"></span>
                        </div>
                        <label class="dt-btn" style="cursor:pointer">
                            Cambiar foto
                            <input type="file" accept="image/*" @change="abrirRecorte" style="display:none">
                        </label>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px">
                        <div><label class="dt-label" for="c-nombre">Nombre público</label><input id="c-nombre" type="text" x-model="datos.nombre" class="dt-input"></div>
                        <div><label class="dt-label" for="c-correo">Correo de contacto</label><input id="c-correo" type="email" x-model="datos.correo_contacto" class="dt-input"></div>
                    </div>
                    <div style="margin-top:14px"><label class="dt-label" for="c-prof">Título o cargo</label><input id="c-prof" type="text" x-model="datos.profesion" class="dt-input"></div>
                    <div style="margin-top:14px"><label class="dt-label" for="c-bio">Biografía</label><textarea id="c-bio" x-model="datos.bio" rows="4" class="dt-input"></textarea></div>
                </section>

                <section class="dt-panel" style="margin:0">
                    <div class="dt-panel-h"><h2>Galería</h2><span class="dt-chip" x-text="`${galeria.length} / 6`"></span></div>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:10px" x-show="galeria.length > 0">
                        <template x-for="(img, index) in galeria" :key="index">
                            <div style="position:relative;aspect-ratio:1;border-radius:8px;overflow:hidden;border:1px solid var(--border)">
                                <img :src="img" alt="" style="width:100%;height:100%;object-fit:cover">
                                <button @click="eliminarFoto(index)" class="dt-ico" style="position:absolute;top:6px;right:6px;width:26px;height:26px" aria-label="Quitar foto">&times;</button>
                            </div>
                        </template>
                    </div>
                    <label x-show="galeria.length < 6" class="dt-new" style="aspect-ratio:auto;height:64px;display:grid;place-items:center;position:relative">
                        <input type="file" multiple accept="image/*" @change="cargarGaleria" style="position:absolute;inset:0;opacity:0;cursor:pointer">
                        <span style="font-size:13px;font-weight:600">+ Añadir fotos</span>
                    </label>
                </section>
            </div>

            <div style="display:flex;flex-direction:column;gap:18px">
                <section class="dt-panel" style="margin:0">
                    <div class="dt-panel-h"><h2>Contacto</h2></div>
                    <div style="display:flex;flex-direction:column;gap:12px">
                        <div class="dt-inline"><span>Teléfono</span><input type="text" x-model="datos.telefono" aria-label="Teléfono"></div>
                        <div class="dt-inline"><span>Dirección</span><input type="text" x-model="datos.direccion" aria-label="Dirección"></div>
                    </div>
                </section>

                <section class="dt-panel" style="margin:0">
                    <div class="dt-panel-h"><h2>Redes sociales</h2><span class="dt-chip" x-text="`${redes.length}/4`"></span></div>
                    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:10px">
                        <template x-for="(red, index) in redes" :key="index">
                            <div class="dt-inline">
                                <select x-model="red.plataforma" aria-label="Red social">
                                    <option value="Instagram">Instagram</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="TikTok">TikTok</option>
                                    <option value="LinkedIn">LinkedIn</option>
                                    <option value="X (Twitter)">X (Twitter)</option>
                                    <option value="YouTube">YouTube</option>
                                </select>
                                <input type="text" x-model="red.usuario" placeholder="@usuario o https://" aria-label="Usuario">
                                <button @click="quitarRed(index)" style="border:0;background:none;padding:0 12px;color:var(--danger);font-size:18px;cursor:pointer" aria-label="Quitar red">&times;</button>
                            </div>
                        </template>
                    </div>
                    <button x-show="redes.length < 4" @click="agregarRed" class="dt-btn" style="width:100%;border-style:dashed">+ Añadir red social</button>
                </section>

                <section class="dt-panel" style="margin:0">
                    <div class="dt-panel-h"><h2>Pagos rápidos</h2></div>
                    {{-- País de cobro: Nequi y Llave Bre-B en Colombia; Zelle, Cash App y Venmo en EE. UU. --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px" role="radiogroup" aria-label="País de tus cobros">
                        <template x-for="(nombrePais, codigo) in mercados" :key="codigo">
                            <button type="button" class="dt-btn" :class="pais === codigo ? 'pri' : ''" role="radio" :aria-checked="pais === codigo" @click="pais = codigo" x-text="nombrePais"></button>
                        </template>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:12px">
                        <template x-for="tipo in (porPais[pais] || [])" :key="tipo">
                            <div class="dt-inline"><span :style="'color:' + mediosPago[tipo].acento" x-text="mediosPago[tipo].nombre"></span><input type="text" x-model="pagos[tipo]" :placeholder="mediosPago[tipo].placeholder" :inputmode="mediosPago[tipo].inputmode" :aria-label="mediosPago[tipo].nombre"></div>
                        </template>
                    </div>
                </section>
            </div>
        </div>

        <div x-show="cropperModalAbierto" x-cloak style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:150;display:grid;place-items:center;padding:16px">
            <div class="dt-panel" style="width:100%;max-width:420px;display:flex;flex-direction:column;align-items:center">
                <h2 style="margin:0 0 14px;font-size:16px">Encuadra tu foto de perfil</h2>
                <div style="width:100%;max-width:300px;aspect-ratio:1;background:#000;border-radius:10px;overflow:hidden;margin-bottom:16px">
                    <img id="imagen-cropper" :src="imgParaRecortar" alt="" style="max-width:100%;display:block">
                </div>
                <div style="display:flex;gap:10px;width:100%">
                    <button @click="cancelarRecorte" class="dt-btn" style="flex:1">Cancelar</button>
                    <button @click="aplicarRecorte" class="dt-btn pri" style="flex:1">Confirmar recorte</button>
                </div>
            </div>
        </div>

        <div class="dt-toast" :class="avisoError ? 'err' : ''" x-show="aviso" x-transition x-cloak role="status" x-text="aviso"></div>
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
                    titulo_galeria: db.titulo_galeria || 'Servicios'
                },

                // Medios de pago por país (config/pagos.php)
                mercados: @json($mercados ?? []),
                porPais: @json(config('pagos.por_pais')),
                mediosPago: @json(config('pagos.medios')),
                pais: db.pais || 'CO',
                pagos: (db.payment_methods || []).reduce((o, m) => ({ ...o, [m.type]: m.value }), { nequi: '', llave_breb: '', zelle: '', cashapp: '', venmo: '' }),
                get listaPagos() {
                    return (this.porPais[this.pais] || []).filter(t => String(this.pagos[t] || '').trim()).map(t => ({ type: t, value: String(this.pagos[t]).trim() }));
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

                // Cada foto se reduce antes de guardarla (partials/reducir-imagen)
                async cargarGaleria(e) {
                    const files = Array.from(e.target.files || []);
                    e.target.value = '';
                    for (const archivo of files.slice(0, 6 - this.galeria.length)) {
                        try {
                            const img = await dtReducirImagen(archivo);
                            if (this.galeria.length < 6) this.galeria.push(img);
                        } catch (error) { this.avisar('No pudimos leer una de las imágenes. Prueba con otra foto.', true); }
                    }
                },
                eliminarFoto(index) { this.galeria.splice(index, 1); },

                async guardarPerfil() {
                    this.guardando = true;
                    let formData = new FormData();
                    // Editar el perfil cargado en vez de crear uno nuevo
                    // El usuario (nickname) no se cambia aquí: el servidor conserva el actual
                    formData.append('perfil_id', window.perfilCargado.id || 'null');
                    formData.append('nombre', this.datos.nombre);
                    formData.append('correo_contacto', this.datos.correo_contacto);
                    formData.append('profesion', this.datos.profesion);
                    formData.append('bio', this.datos.bio);
                    formData.append('telefono', this.datos.telefono);
                    formData.append('direccion', this.datos.direccion);
                    formData.append('pais', this.pais);
                    formData.append('payment_methods', JSON.stringify(this.listaPagos));
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
                        
                        let resultado = respuesta.status === 413
                            ? { message: 'Las fotos pesan demasiado para guardarlas juntas. Usa imágenes más livianas o sube menos a la vez.' }
                            : await respuesta.json();
                        if(resultado.status === 'success') {
                            this.avisar('Cambios guardados');
                        } else {
                            this.avisar(resultado.message || 'No se pudieron guardar los cambios.', true);
                        }
                    } catch (error) { this.avisar('Sin conexión con el servidor.', true); }
                    this.guardando = false;
                },

                aviso: '', avisoError: false, avisoT: null,
                avisar(mensaje, error = false) {
                    this.aviso = mensaje; this.avisoError = error;
                    clearTimeout(this.avisoT); this.avisoT = setTimeout(() => this.aviso = '', 3000);
                }
            }
        }
    </script>
    @include('partials.reducir-imagen')
@endsection
