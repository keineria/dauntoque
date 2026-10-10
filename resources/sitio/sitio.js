// Sitio de ventas DaUnToque: videos perezosos, visor de perfiles, calculadora y botón fijo. Sin dependencias.
(function () {
  var reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var ahorro = navigator.connection && navigator.connection.saveData;

  // Videos: arrancan solo al entrar en pantalla y no se cargan con ahorro de datos
  var videos = document.querySelectorAll('[data-video]');
  if (videos.length && 'IntersectionObserver' in window && !reducir && !ahorro) {
    var obs = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (e) {
        var marco = e.target, v = marco.querySelector('video');
        if (e.isIntersecting) {
          if (!v) {
            v = document.createElement('video');
            v.muted = true; v.loop = true; v.playsInline = true; v.setAttribute('playsinline', ''); v.setAttribute('aria-hidden', 'true');
            ['webm', 'mp4'].forEach(function (t) { var s = document.createElement('source'); s.src = marco.dataset.video + '.' + t; s.type = 'video/' + t; v.appendChild(s); });
            v.addEventListener('playing', function () { marco.classList.add('reproduciendo'); });
            v.addEventListener('pause', function () { marco.classList.remove('reproduciendo'); });
            marco.insertBefore(v, marco.firstChild);
          }
          var p = v.play(); if (p && p.catch) p.catch(function () {});
        } else if (v) { v.pause(); }
      });
    }, { rootMargin: '120px 0px', threshold: 0.25 });
    videos.forEach(function (m) { obs.observe(m); });
  }

  // Elección de país e idioma: se recuerda para la próxima visita
  document.querySelectorAll('[data-version]').forEach(function (a) {
    a.addEventListener('click', function () { try { localStorage.setItem('dt-version', a.dataset.version); } catch (e) {} });
  });
  try { localStorage.setItem('dt-version', document.documentElement.dataset.version); } catch (e) {}
  document.addEventListener('click', function (e) {
    document.querySelectorAll('details.selector[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });

  // Visor de perfiles reales: el iframe se carga al llegar a la sección
  var visor = document.querySelector('[data-visor]');
  if (visor) {
    var marco = visor.querySelector('iframe'), abrir = visor.querySelector('.visor-abrir');
    var botones = visor.querySelectorAll('[data-perfil]');
    var mostrar = function (b) {
      botones.forEach(function (x) { x.setAttribute('aria-selected', x === b ? 'true' : 'false'); });
      marco.src = b.dataset.perfil; abrir.href = b.dataset.perfil;
    };
    botones.forEach(function (b) { b.addEventListener('click', function () { mostrar(b); }); });
    var cargado = false;
    var ov = new IntersectionObserver(function (e) { if (e[0].isIntersecting && !cargado) { cargado = true; mostrar(botones[0]); ov.disconnect(); } }, { rootMargin: '300px 0px' });
    ov.observe(visor);
  }

  // Calculadora de ahorro: papel al año frente al pago único
  var calc = document.querySelector('[data-calc]');
  if (calc) {
    var fmt = new Intl.NumberFormat(calc.dataset.locale, { style: 'currency', currency: calc.dataset.moneda, maximumFractionDigits: 0 });
    var tarjetas = calc.querySelector('[name=tarjetas]'), costo = calc.querySelector('[name=costo]'), salida = calc.querySelector('[data-anio]');
    var calcular = function () {
      var anual = Math.max(0, +tarjetas.value || 0) * 12 / 100 * Math.max(0, +costo.value || 0);
      salida.textContent = fmt.format(anual);
    };
    tarjetas.addEventListener('input', calcular); costo.addEventListener('input', calcular); calcular();
  }

  // Botón fijo de compra en el celular: se esconde cuando ya hay un botón de compra en pantalla
  var fija = document.querySelector('.compra-fija');
  var anclas = document.querySelectorAll('[data-compra]');
  if (fija && anclas.length && 'IntersectionObserver' in window) {
    var visibles = new Set();
    var of = new IntersectionObserver(function (es) {
      es.forEach(function (e) { e.isIntersecting ? visibles.add(e.target) : visibles.delete(e.target); });
      fija.classList.toggle('fuera', visibles.size > 0);
    });
    anclas.forEach(function (a) { of.observe(a); });
  }

  // Dos perfiles: el ejecutivo o una pasión de fin de semana; rota solo hasta que la persona toque
  var dos = document.querySelector('[data-dos]');
  if (dos) {
    var pasion = 'moto', giro = null, paso = 0, orden = ['ejec', 'moto', 'ejec', 'escalada', 'ejec', 'gym'];
    var verVista = function (v) {
      var personal = v !== 'ejec';
      if (personal) pasion = v;
      dos.querySelectorAll('[data-vista]').forEach(function (x) { x.hidden = x.dataset.vista !== v; });
      dos.querySelectorAll('[data-modo]').forEach(function (b) { b.setAttribute('aria-pressed', String((b.dataset.modo === 'personal') === personal)); });
      dos.querySelectorAll('[data-pasion]').forEach(function (b) { b.setAttribute('aria-pressed', String(personal && b.dataset.pasion === v)); });
    };
    var parar = function () { clearInterval(giro); };
    dos.querySelectorAll('[data-modo]').forEach(function (b) {
      b.addEventListener('click', function () { parar(); verVista(b.dataset.modo === 'personal' ? pasion : 'ejec'); });
    });
    dos.querySelectorAll('[data-pasion]').forEach(function (b) {
      b.addEventListener('click', function () { parar(); verVista(b.dataset.pasion); });
    });
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
      giro = setInterval(function () { paso = (paso + 1) % orden.length; verVista(orden[paso]); }, 3500);
    }
  }
})();
