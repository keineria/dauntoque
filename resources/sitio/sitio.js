// Sitio de ventas DaUnToque: videos perezosos, demo "Pruébalo", dos perfiles, tarjeta con tu nombre,
// calculadora y barra fija de compra. Sin dependencias. Cada bloque usa const/let propios para no pisar nombres.
(function () {
  'use strict';
  const reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const ahorroDatos = navigator.connection && navigator.connection.saveData;
  const hayIO = 'IntersectionObserver' in window;

  // Videos: arrancan solo al entrar en pantalla y no se cargan con ahorro de datos ni movimiento reducido
  const marcosVideo = document.querySelectorAll('[data-video]');
  if (marcosVideo.length && hayIO && !reducir && !ahorroDatos) {
    const obsVideo = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (e) {
        const marco = e.target;
        let v = marco.querySelector('video');
        if (e.isIntersecting) {
          if (!v) {
            v = document.createElement('video');
            v.muted = true; v.loop = true; v.playsInline = true;
            v.setAttribute('playsinline', ''); v.setAttribute('aria-hidden', 'true');
            ['webm', 'mp4'].forEach(function (tipo) {
              const s = document.createElement('source');
              s.src = marco.dataset.video + '.' + tipo; s.type = 'video/' + tipo; v.appendChild(s);
            });
            v.addEventListener('playing', function () { marco.classList.add('reproduciendo'); });
            v.addEventListener('pause', function () { marco.classList.remove('reproduciendo'); });
            marco.insertBefore(v, marco.firstChild);
          }
          const p = v.play(); if (p && p.catch) p.catch(function () {});
        } else if (v) { v.pause(); }
      });
    }, { rootMargin: '120px 0px', threshold: 0.25 });
    marcosVideo.forEach(function (m) { obsVideo.observe(m); });
  }

  // Elección de país e idioma: se recuerda para la próxima visita
  document.querySelectorAll('a[data-version]').forEach(function (a) {
    a.addEventListener('click', function () { try { localStorage.setItem('dt-version', a.dataset.version); } catch (e) {} });
  });
  try { localStorage.setItem('dt-version', document.documentElement.dataset.version); } catch (e) {}
  document.addEventListener('click', function (e) {
    document.querySelectorAll('details.selector[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });

  // Calculadora de ahorro: papel al año frente al pago único
  const calc = document.querySelector('[data-calc]');
  if (calc) {
    const fmtCalc = new Intl.NumberFormat(calc.dataset.locale, { style: 'currency', currency: calc.dataset.moneda, maximumFractionDigits: 0 });
    const inTarjetas = calc.querySelector('[name=tarjetas]'), inCosto = calc.querySelector('[name=costo]'), salidaAnio = calc.querySelector('[data-anio]');
    const calcular = function () {
      const anual = Math.max(0, +inTarjetas.value || 0) * 12 / 100 * Math.max(0, +inCosto.value || 0);
      salidaAnio.textContent = fmtCalc.format(anual);
    };
    inTarjetas.addEventListener('input', calcular); inCosto.addEventListener('input', calcular); calcular();
  }

  // Barra fija de compra en el celular: se esconde cuando ya hay un botón de compra en pantalla
  const barra = document.querySelector('.barra-movil');
  const anclasCompra = document.querySelectorAll('[data-compra]');
  if (barra && anclasCompra.length && hayIO) {
    const enPantalla = new Set();
    const obsCompra = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) enPantalla.add(e.target); else enPantalla.delete(e.target); });
      barra.classList.toggle('fuera', enPantalla.size > 0);
    });
    anclasCompra.forEach(function (a) { obsCompra.observe(a); });
  }

  // Pruébalo: cada botón del perfil avisa en el celular y suma en el panel del dueño
  const demo = document.querySelector('[data-demo]');
  if (demo) {
    const feed = demo.querySelector('.feed'), avisoTel = demo.querySelector('.tel-toast');
    const ahora = demo.dataset.ahora, hace = demo.dataset.hace;
    let relojAviso = null, abierto = false;
    const sube = function (k) {
      const el = demo.querySelector('[data-k="' + k + '"]');
      if (!el) return;
      el.textContent = String(+el.textContent + 1);
      el.classList.add('sube');
      setTimeout(function () { el.classList.remove('sube'); }, 900);
    };
    const evento = function (ico, txt) {
      const li = document.createElement('li');
      const i = document.createElement('i'), s = document.createElement('span'), tm = document.createElement('time');
      i.textContent = ico; s.textContent = txt; tm.textContent = ahora;
      li.append(i, s, tm);
      feed.prepend(li);
      Array.prototype.slice.call(feed.querySelectorAll('time'), 1, 3).forEach(function (t, n) {
        if (t.textContent === ahora) t.textContent = hace.replace(':n', String((n + 1) * 2));
      });
      while (feed.children.length > 7) feed.lastElementChild.remove();
    };
    const avisar = function (txt) {
      avisoTel.querySelector('span').textContent = txt;
      avisoTel.classList.add('on');
      clearTimeout(relojAviso);
      relojAviso = setTimeout(function () { avisoTel.classList.remove('on'); }, 2200);
    };
    demo.querySelectorAll('[data-acc]').forEach(function (b) {
      b.addEventListener('click', function () {
        if (!abierto) { abierto = true; sube('vis'); evento('👀', demo.dataset.abrio); }
        if (b.dataset.copiar && navigator.clipboard) navigator.clipboard.writeText(b.dataset.copiar).catch(function () {});
        avisar(b.dataset.aviso);
        if (b.dataset.kpi) sube(b.dataset.kpi);
        evento(b.dataset.ico, b.dataset.evento);
      });
    });
  }

  // Dos perfiles: el ejecutivo o una pasión de fin de semana; rota solo hasta que la persona toque
  const dos = document.querySelector('[data-dos]');
  if (dos) {
    const ordenDos = ['ejec', 'moto', 'ejec', 'escalada', 'ejec', 'gym'];
    let pasion = 'moto', giroDos = null, pasoDos = 0;
    const verVista = function (v) {
      const personal = v !== 'ejec';
      if (personal) pasion = v;
      dos.querySelectorAll('[data-vista]').forEach(function (x) { x.hidden = x.dataset.vista !== v; });
      dos.querySelectorAll('[data-modo]').forEach(function (b) { b.setAttribute('aria-pressed', String((b.dataset.modo === 'personal') === personal)); });
      dos.querySelectorAll('[data-pasion]').forEach(function (b) { b.setAttribute('aria-pressed', String(personal && b.dataset.pasion === v)); });
    };
    const pararDos = function () { clearInterval(giroDos); giroDos = null; };
    dos.querySelectorAll('[data-modo]').forEach(function (b) {
      b.addEventListener('click', function () { pararDos(); verVista(b.dataset.modo === 'personal' ? pasion : 'ejec'); });
    });
    dos.querySelectorAll('[data-pasion]').forEach(function (b) {
      b.addEventListener('click', function () { pararDos(); verVista(b.dataset.pasion); });
    });
    if (!reducir) {
      giroDos = setInterval(function () { pasoDos = (pasoDos + 1) % ordenDos.length; verVista(ordenDos[pasoDos]); }, 3500);
    }
  }

  // Tu tarjeta con tu nombre: acabado Matte Black o Crystal y la tarjeta se inclina con el puntero
  const estudioBox = document.querySelector('[data-estudio]');
  if (estudioBox) {
    const escenario = estudioBox.querySelector('.estudio'), tarjeta3d = estudioBox.querySelector('.t3d');
    const nombreTarjeta = estudioBox.querySelector('[data-nombre]'), acabadoTarjeta = estudioBox.querySelector('[data-car]');
    const campoNombre = estudioBox.querySelector('input');
    campoNombre.addEventListener('input', function () {
      nombreTarjeta.textContent = campoNombre.value.trim() || estudioBox.dataset.vacio;
    });
    estudioBox.querySelectorAll('[data-acab]').forEach(function (b) {
      b.addEventListener('click', function () {
        const k = b.dataset.acab;
        estudioBox.querySelectorAll('[data-acab]').forEach(function (x) { x.setAttribute('aria-pressed', String(x === b)); });
        escenario.classList.toggle('crystal', k === 'crystal');
        acabadoTarjeta.textContent = b.textContent.trim();
        estudioBox.querySelectorAll('[data-acab-txt]').forEach(function (x) { x.hidden = x.dataset.acabTxt !== k; });
      });
    });
    if (!reducir) {
      escenario.addEventListener('pointermove', function (e) {
        const r = escenario.getBoundingClientRect(), px = (e.clientX - r.left) / r.width - 0.5, py = (e.clientY - r.top) / r.height - 0.5;
        tarjeta3d.style.transform = 'rotateX(' + (-py * 24) + 'deg) rotateY(' + (px * 30) + 'deg)';
      });
      escenario.addEventListener('pointerleave', function () { tarjeta3d.style.transform = ''; });
    }
  }
  // Casos reales: carrusel de 4 por vista que avanza solo en bucle y se detiene con el mouse, el foco o el dedo
  const carrusel = document.querySelector('[data-carrusel]');
  if (carrusel) {
    const originales = Array.prototype.slice.call(carrusel.children);
    // Copias de las primeras tarjetas al final: al llegar a ellas se salta sin animación al inicio real
    originales.slice(0, 4).forEach(function (t) {
      const copia = t.cloneNode(true); copia.setAttribute('aria-hidden', 'true');
      copia.querySelectorAll('a').forEach(function (a) { a.tabIndex = -1; });
      carrusel.appendChild(copia);
    });
    let pausado = false;
    const paso = function () { return originales[1].offsetLeft - originales[0].offsetLeft; };
    const avanzar = function () {
      if (pausado || document.hidden) return;
      const fin = originales.length * paso();
      if (carrusel.scrollLeft >= fin - 2) carrusel.scrollTo({ left: carrusel.scrollLeft - fin, behavior: 'auto' });
      carrusel.scrollTo({ left: carrusel.scrollLeft + paso(), behavior: 'smooth' });
    };
    carrusel.addEventListener('scroll', function () {
      const fin = originales.length * paso();
      if (carrusel.scrollLeft >= fin - 2) carrusel.scrollTo({ left: carrusel.scrollLeft - fin, behavior: 'auto' });
    }, { passive: true });
    ['mouseenter', 'focusin', 'touchstart'].forEach(function (ev) { carrusel.addEventListener(ev, function () { pausado = true; }, { passive: true }); });
    ['mouseleave', 'focusout'].forEach(function (ev) { carrusel.addEventListener(ev, function () { pausado = false; }); });
    carrusel.addEventListener('touchend', function () { setTimeout(function () { pausado = false; }, 4000); }, { passive: true });
    if (!reducir) setInterval(avanzar, 3500);
  }
})();
