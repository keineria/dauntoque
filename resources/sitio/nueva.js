// Portada nueva del sitio de ventas (prueba): tarjeta con tu nombre, explorador de módulos, panel, IA
// y avisos de compras reales (/compras-recientes.json). Sin dependencias.
(function () {
  'use strict';
  const d = document;
  const reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hayIO = 'IntersectionObserver' in window;
  const $ = function (s, r) { return (r || d).querySelector(s); };
  const $$ = function (s, r) { return Array.prototype.slice.call((r || d).querySelectorAll(s)); };
  d.documentElement.classList.add('js');

  // Barra superior: fondo al bajar; el menú del celular se cierra al elegir o tocar fuera
  const top = $('[data-top]');
  const pegar = function () { top.classList.toggle('pegada', window.scrollY > 12); };
  if (top) { pegar(); window.addEventListener('scroll', pegar, { passive: true }); }
  d.addEventListener('click', function (e) {
    $$('details.menu[open]').forEach(function (m) { if (!m.contains(e.target) || e.target.closest('nav a')) m.removeAttribute('open'); });
  });

  // Aparición de las secciones al bajar
  const porVer = $$('.ver');
  if (hayIO && !reducir) {
    const obsVer = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('visto'); obsVer.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    porVer.forEach(function (el) { obsVer.observe(el); });
  } else { porVer.forEach(function (el) { el.classList.add('visto'); }); }

  // Tarjeta del héroe: se inclina con el puntero, lleva el nombre que escriben y cambia de acabado
  const estudio = $('[data-estudio]');
  if (estudio) {
    const tarjeta = $('[data-tarjeta]', estudio), zona = $('[data-inclinar]', estudio);
    const nombre = $('[data-nombre]', estudio), entrada = $('[data-nombre-in]', estudio), acabadoTxt = $('[data-acabado-txt]', estudio);
    if (!reducir && window.matchMedia('(hover: hover)').matches) {
      zona.addEventListener('pointermove', function (e) {
        const r = zona.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
        tarjeta.classList.add('manual');
        tarjeta.style.setProperty('--ry', ((x - 0.5) * 26).toFixed(1) + 'deg');
        tarjeta.style.setProperty('--rx', ((0.5 - y) * 20).toFixed(1) + 'deg');
        tarjeta.style.setProperty('--gx', (x * 100).toFixed(0) + '%');
        tarjeta.style.setProperty('--gy', (y * 100).toFixed(0) + '%');
      });
      zona.addEventListener('pointerleave', function () {
        ['--rx', '--ry', '--gx', '--gy'].forEach(function (p) { tarjeta.style.removeProperty(p); });
        tarjeta.classList.remove('manual');
      });
    }
    entrada.addEventListener('input', function () { nombre.textContent = entrada.value.trim() || nombre.dataset.vacio; });
    $$('[data-acabado-btn]', estudio).forEach(function (b) {
      b.addEventListener('click', function () {
        const a = b.dataset.acabadoBtn;
        $$('[data-acabado-btn]', estudio).forEach(function (o) { o.setAttribute('aria-pressed', String(o === b)); });
        tarjeta.dataset.acabado = a;
        acabadoTxt.textContent = b.textContent;
        // Los botones de compra en línea llevan el acabado elegido (los de WhatsApp no cambian)
        $$('a[data-cta]').forEach(function (cta) {
          if (/\/comprar(\?|$)/.test(cta.getAttribute('href'))) cta.setAttribute('href', cta.getAttribute('href').split('?')[0] + '?acabado=' + a);
        });
      });
    });
    // Lo que pasa después del toque: los avisos de ejemplo se turnan alrededor de la tarjeta
    const eventos = $$('.evento', estudio);
    if (eventos.length && !reducir) {
      let turno = 0;
      const mostrar = function () {
        eventos.forEach(function (ev, n) { ev.classList.toggle('on', n === turno % eventos.length || n === (turno + 1) % eventos.length); });
        turno++;
      };
      mostrar(); setInterval(mostrar, 2600);
    }
  }

  // Explorador de módulos: pestañas con su demo en el celular; rota solo hasta que la persona lo use
  const exp = $('[data-explorador]');
  if (exp) {
    const tabs = $$('[role=tab]', exp), aviso = $('[data-aviso]', exp);
    let giro = null, relojAviso = null, actual = 0;
    const ver = function (clave, enfocar) {
      tabs.forEach(function (t, n) {
        const on = t.dataset.mod === clave;
        if (on) actual = n;
        t.setAttribute('aria-selected', String(on));
        t.tabIndex = on ? 0 : -1;
        if (on && enfocar) t.focus();
        if (on && t.scrollIntoView && window.innerWidth < 900 && enfocar !== null) {
          const lista = t.parentElement;
          lista.scrollTo({ left: t.offsetLeft - 20, behavior: reducir ? 'auto' : 'smooth' });
        }
      });
      $$('[data-pan]', exp).forEach(function (p) { p.hidden = p.dataset.pan !== clave; });
      $$('[data-info]', exp).forEach(function (p) { p.hidden = p.dataset.info !== clave; });
      aviso.classList.remove('on');
    };
    const parar = function () { clearInterval(giro); giro = null; };
    const avisar = function (txt) {
      $('span', aviso).textContent = txt;
      aviso.classList.add('on');
      clearTimeout(relojAviso);
      relojAviso = setTimeout(function () { aviso.classList.remove('on'); }, 2300);
    };
    tabs.forEach(function (t, n) {
      t.addEventListener('click', function () { parar(); ver(t.dataset.mod); });
      t.addEventListener('keydown', function (e) {
        const paso = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
        if (!paso) return;
        e.preventDefault(); parar();
        ver(tabs[(n + paso + tabs.length) % tabs.length].dataset.mod, true);
      });
    });
    exp.addEventListener('pointerdown', parar);
    exp.addEventListener('focusin', parar);
    if (hayIO && !reducir) {
      new IntersectionObserver(function (es) {
        if (es[0].isIntersecting && !giro && !exp.dataset.usado) {
          giro = setInterval(function () { ver(tabs[(actual + 1) % tabs.length].dataset.mod, null); }, 4200);
        } else if (!es[0].isIntersecting) { parar(); }
      }, { threshold: 0.35 }).observe(exp);
      ['pointerdown', 'focusin'].forEach(function (ev) { exp.addEventListener(ev, function () { exp.dataset.usado = '1'; }); });
    }

    $$('[data-toque]', exp).forEach(function (b) { b.addEventListener('click', function () { avisar(b.dataset.toque); }); });
    $$('[data-ir]', exp).forEach(function (b) { b.addEventListener('click', function () { ver(b.dataset.ir); }); });

    // Agenda: día y hora, y la reserva de ejemplo
    const reservar = $('[data-reservar]', exp), reservado = $('[data-reservado]', exp);
    const elegido = function (grupo) { const b = $(grupo + ' [aria-pressed="true"]', exp); return b ? (b.dataset.dia || b.dataset.hora) : null; };
    const marcar = function (b) {
      $$('button', b.parentElement).forEach(function (o) { o.setAttribute('aria-pressed', String(o === b)); });
      reservado.hidden = true; reservar.hidden = false;
      const hora = elegido('.ag-horas');
      reservar.disabled = !hora;
      reservar.textContent = hora ? 'Reservar ' + elegido('.ag-dias') + ' · ' + hora : 'Elige una hora';
    };
    $$('.ag-dias button, .ag-horas button', exp).forEach(function (b) { b.addEventListener('click', function () { marcar(b); }); });
    reservar.addEventListener('click', function () {
      $('span', reservado).textContent = 'Cita reservada: ' + elegido('.ag-dias') + ', ' + elegido('.ag-horas') + '. Te llega la confirmación al correo.';
      reservado.hidden = false; reservar.hidden = true;
    });

    // Califícame: las buenas invitan a reseñar en Google; las demás llegan en privado
    const estrellas = $$('[data-estrella]', exp), calRes = $('[data-cal-res]', exp), calGoogle = $('[data-cal-google]', exp);
    estrellas.forEach(function (b) {
      b.addEventListener('click', function () {
        const n = +b.dataset.estrella;
        estrellas.forEach(function (o) { const on = +o.dataset.estrella <= n; o.classList.toggle('on', on); o.setAttribute('aria-pressed', String(+o.dataset.estrella === n)); });
        calRes.textContent = n >= 4 ? '¡Gracias! Tu calificación se muestra en el perfil.' : 'Gracias. Tu comentario le llega en privado al dueño.';
        calGoogle.hidden = n < 4;
      });
    });

    // Música: reproductor de ejemplo (sin sonido)
    const musica = $('[data-musica]', exp);
    $('button', musica).addEventListener('click', function () {
      this.setAttribute('aria-pressed', String(musica.classList.toggle('suena')));
    });

    // Dos perfiles: ejecutivo o personal
    $$('[data-cara]', exp).forEach(function (b) {
      b.addEventListener('click', function () {
        $$('[data-cara]', exp).forEach(function (o) { o.setAttribute('aria-pressed', String(o === b)); });
        $$('[data-vista-cara]', exp).forEach(function (c) { c.hidden = c.dataset.vistaCara !== b.dataset.cara; });
      });
    });
  }

  // Panel: los números suben al entrar en pantalla
  const tablero = $('[data-contar]');
  if (tablero && hayIO && !reducir) {
    const fmt = new Intl.NumberFormat('es-CO');
    new IntersectionObserver(function (es, obs) {
      if (!es[0].isIntersecting) return;
      obs.disconnect();
      $$('[data-hasta]', tablero).forEach(function (el) {
        const hasta = +el.dataset.hasta, dec = el.dataset.tipo === 'dec', inicio = performance.now();
        const paso = function (ahora) {
          const p = Math.min(1, (ahora - inicio) / 1400), v = hasta * (1 - Math.pow(1 - p, 3));
          el.textContent = dec ? (v / 10).toFixed(1).replace('.', ',') : fmt.format(Math.round(v));
          if (p < 1) requestAnimationFrame(paso);
        };
        requestAnimationFrame(paso);
      });
    }, { threshold: 0.3 }).observe(tablero);
  }

  // IA: la persona escribe su oficio y la IA redacta la presentación (animación de ejemplo)
  const ia = $('[data-ia]');
  if (ia && hayIO && !reducir) {
    const pide = $('[data-ia-pide]', ia), responde = $('[data-ia-responde]', ia);
    const escribir = function (el, texto, ms, fin) {
      let n = 0; el.textContent = ''; el.classList.add('escribe');
      const reloj = setInterval(function () {
        el.textContent = texto.slice(0, ++n);
        if (n >= texto.length) { clearInterval(reloj); el.classList.remove('escribe'); if (fin) fin(); }
      }, ms);
    };
    new IntersectionObserver(function (es, obs) {
      if (!es[0].isIntersecting) return;
      obs.disconnect();
      responde.textContent = '';
      escribir(pide, ia.dataset.pide, 32, function () { setTimeout(function () { escribir(responde, ia.dataset.responde, 18); }, 500); });
    }, { threshold: 0.5 }).observe(ia);
  }

  // Barra fija de compra del celular: se esconde cuando ya hay un botón de compra en pantalla
  const barra = $('[data-barra]'), anclas = $$('[data-compra]');
  if (barra && anclas.length && hayIO) {
    const enPantalla = new Set();
    const obsCompra = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) enPantalla.add(e.target); else enPantalla.delete(e.target); });
      barra.classList.toggle('fuera', enPantalla.size > 0);
    });
    anclas.forEach(function (a) { obsCompra.observe(a); });
  }

  // Avisos de compras REALES: pedidos pagados que entrega /compras-recientes.json (nombre e inicial, ciudad y hora real).
  // Si no hay compras recientes no se muestra nada. Salen de a uno, con pausas, y se pueden cerrar.
  const caja = $('[data-compras]');
  const cerrado = function () { try { return sessionStorage.getItem('dt-compras') === 'no'; } catch (e) { return false; } };
  // Modo demostración (solo con ?demo=avisos en la dirección): nombres de EJEMPLO para enseñar cómo se ven los avisos.
  // Cada aviso lleva la etiqueta «Demostración»; un visitante normal nunca los ve.
  const demo = /[?&]demo=avisos(&|$)/.test(location.search);
  const ejemplos = function () {
    const nombres = ['Luis P.', 'Carolina R.', 'Andrés M.', 'Valentina G.', 'Juan D.', 'Daniela C.', 'Santiago H.', 'Laura V.', 'Camilo T.', 'Natalia B.', 'Felipe O.', 'Paula S.', 'Sebastián L.', 'Mariana Z.', 'Diego A.', 'Sara E.', 'Mateo J.', 'Isabella N.', 'Julián Q.', 'Manuela F.', 'Nicolás U.', 'Gabriela I.', 'Esteban K.', 'Juliana W.', 'David Y.', 'Catalina P.', 'Alejandro R.', 'Luisa M.', 'Jorge G.', 'Ana D.', 'Ricardo C.', 'Angélica H.', 'Óscar V.', 'Tatiana T.', 'Mauricio B.', 'Lina O.', 'Germán S.', 'Yuliana L.', 'Fabián Z.', 'Marcela A.', 'Hernán E.', 'Viviana J.', 'Cristian N.', 'Diana Q.', 'Iván F.', 'Mónica U.', 'Wilson I.', 'Adriana K.'];
    const ciudades = ['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena', 'Bucaramanga', 'Pereira', 'Manizales', 'Cúcuta', 'Ibagué', 'Santa Marta', 'Villavicencio', 'Armenia', 'Pasto', 'Neiva', 'Montería', 'Popayán', 'Tunja', 'Envigado', 'Rionegro'];
    const ahora = Date.now() / 1000;
    return nombres.map(function (nombre, n) {
      return { nombre: nombre, ciudad: ciudades[(n * 7) % ciudades.length], producto: n % 3 === 1 ? 'Crystal' : 'Matte Black', t: ahora - n * 12 * 60 };
    });
  };
  if (caja && window.fetch && (demo || !cerrado())) {
    const hace = function (t) {
      const s = Math.max(0, Date.now() / 1000 - t), min = Math.floor(s / 60), h = Math.floor(min / 60), dias = Math.floor(h / 24);
      if (min < 3) return 'hace un instante';
      if (min < 60) return 'hace ' + min + ' min';
      if (h < 24) return 'hace ' + h + (h === 1 ? ' hora' : ' horas');
      return dias === 1 ? 'ayer' : 'hace ' + dias + ' días';
    };
    let relojCompras = null, vivo = true;
    const ocultar = function () { caja.classList.remove('on'); };
    $('[data-c-cerrar]', caja).addEventListener('click', function () {
      vivo = false; clearTimeout(relojCompras); ocultar();
      try { sessionStorage.setItem('dt-compras', 'no'); } catch (e) {}
    });
    const rotar = function (compras) {
      let n = 0;
      const siguiente = function () {
        if (!vivo || n >= compras.length) return ocultar();
        const c = compras[n++];
        $('[data-c-quien]', caja).textContent = c.nombre + (c.ciudad ? ' · ' + c.ciudad : '');
        $('[data-c-que]', caja).textContent = 'Compró la tarjeta ' + c.producto;
        $('[data-c-cuando]', caja).textContent = hace(c.t);
        caja.hidden = false;
        requestAnimationFrame(function () { requestAnimationFrame(function () { caja.classList.add('on'); }); });
        relojCompras = setTimeout(function () {
          ocultar();
          // Pausa de 20 a 35 segundos entre un aviso y el siguiente
          relojCompras = setTimeout(siguiente, demo ? 5000 : 20000 + Math.random() * 15000);
        }, demo ? 5000 : 7000);
      };
      relojCompras = setTimeout(siguiente, demo ? 2500 : 9000);
    };
    if (demo) {
      caja.classList.add('es-demo');
      rotar(ejemplos());
    } else fetch(caja.dataset.compras, { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : { compras: [] }; })
      .then(function (datos) { if (datos && datos.compras && datos.compras.length) rotar(datos.compras); })
      .catch(function () {});
  }
})();
