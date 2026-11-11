/* DaUnToque · /etiquetas.js: píxeles conectados desde el Centro de mando, origen de la visita (UTM) y eventos.
   Las páginas mandan eventos con: window.dtq = window.dtq || []; dtq.push(['inicio_compra', {valor: 299900, moneda: 'COP'}]);
   Eventos: 'inicio_compra' y 'compra' ({valor, moneda, id}). La visita se manda sola. */
(function (w, d) {
    'use strict';
    var C = __CONFIG__;
    if (w.__dtEtiquetas) return;
    w.__dtEtiquetas = true;

    function script(src) {
        var s = d.createElement('script'), p = d.getElementsByTagName('script')[0];
        s.async = true; s.src = src;
        (p && p.parentNode ? p.parentNode : d.head).insertBefore(s, p || null);
    }

    /* ---------- Origen de la visita: UTM y clic de anuncio (último toque, 30 días) ---------- */
    var dominio = /(^|\.)dauntoque\.com$/.test(location.hostname) ? '; domain=.dauntoque.com' : '';
    function galleta(n, v, dias) {
        d.cookie = n + '=' + encodeURIComponent(v) + '; path=/; max-age=' + (dias * 86400) + '; SameSite=Lax' + dominio + (location.protocol === 'https:' ? '; Secure' : '');
    }
    function leer(n) {
        var m = d.cookie.match(new RegExp('(?:^|; )' + n + '=([^;]*)'));
        try { return m ? decodeURIComponent(m[1]) : null; } catch (e) { return null; }
    }
    function azar() {
        var b = new Uint8Array(16), t = '';
        (w.crypto || w.msCrypto).getRandomValues(b);
        for (var i = 0; i < b.length; i++) t += ('0' + b[i].toString(16)).slice(-2);
        return t;
    }

    var q = new URLSearchParams(location.search), utm = {};
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'].forEach(function (n) {
        var v = q.get(n);
        if (v) utm[n] = v.slice(0, 100);
    });
    var clic = q.get('gclid') ? 'gclid' : q.get('fbclid') ? 'fbclid' : q.get('ttclid') ? 'ttclid' : null;
    if (clic) {
        utm.clic = clic;
        // Un clic de anuncio sin UTM: se nombra la fuente para no contarlo como visita directa
        if (!utm.utm_source) {
            utm.utm_source = {gclid: 'google', fbclid: 'facebook', ttclid: 'tiktok'}[clic];
            utm.utm_medium = clic === 'gclid' ? 'cpc' : 'paid_social';
        }
    }
    var referente = '';
    try { referente = d.referrer ? new URL(d.referrer).hostname.replace(/^(www|m|l|lm)\./, '') : ''; } catch (e) {}
    if (referente === location.hostname || /(^|\.)dauntoque\.com$/.test(referente)) referente = '';
    // Sin UTM pero llegó desde otro sitio: buscador (orgánico), red social o referido, como lo agrupa Google Analytics
    if (!Object.keys(utm).length && referente) {
        utm = {
            utm_source: referente.slice(0, 100),
            utm_medium: /(^|\.)(google|bing|yahoo|duckduckgo|ecosia|yandex)\./.test(referente) ? 'organic'
                : /(facebook|instagram|tiktok|linkedin|whatsapp|youtube|twitter|pinterest|threads)\.|^t\.co$|^x\.com$/.test(referente) ? 'social' : 'referral'
        };
    }
    if (Object.keys(utm).length) {
        galleta('dt_utm', JSON.stringify(utm), 30);
    } else {
        try { utm = JSON.parse(leer('dt_utm') || '{}') || {}; } catch (e) { utm = {}; }
    }
    var visitante = leer('dt_v');
    if (!/^[a-f0-9]{32}$/.test(visitante || '')) visitante = azar();
    galleta('dt_v', visitante, 365);

    var visita = JSON.stringify({v: visitante, p: location.pathname.slice(0, 190), r: referente.slice(0, 120), u: utm});
    // dt_yo: este navegador es del administrador (lo marca el Centro de mando) y sus visitas no se cuentan
    if (!leer('dt_yo') && !(navigator.sendBeacon && navigator.sendBeacon(C.visita, new Blob([visita], {type: 'text/plain'})))) {
        try { fetch(C.visita, {method: 'POST', body: visita, keepalive: true, credentials: 'same-origin', headers: {'Content-Type': 'text/plain'}}); } catch (e) {}
    }

    /* ---------- Cargadores oficiales de cada píxel (solo los conectados) ---------- */
    w.dataLayer = w.dataLayer || [];
    if (C.gtm) {
        w.dataLayer.push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
        script('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(C.gtm));
    }
    if (C.ga4 || C.gads) {
        w.gtag = w.gtag || function () { w.dataLayer.push(arguments); };
        script('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(C.ga4 || C.gads));
        w.gtag('js', new Date());
        if (C.ga4) w.gtag('config', C.ga4);
        if (C.gads) w.gtag('config', C.gads);
    }
    if (C.meta) {
        !function (f, b, e, v, n, t, s) { if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); }; if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; script(v); }(w, d, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
        w.fbq('init', C.meta);
        w.fbq('track', 'PageView');
    }
    if (C.tiktok) {
        !function (w, d, t) { w.TiktokAnalyticsObject = t; var ttq = w[t] = w[t] || []; ttq.methods = ['page', 'track', 'identify', 'instances', 'debug', 'on', 'off', 'once', 'ready', 'alias', 'group', 'enableCookie', 'disableCookie', 'holdConsent', 'revokeConsent', 'grantConsent']; ttq.setAndDefer = function (t, e) { t[e] = function () { t.push([e].concat(Array.prototype.slice.call(arguments, 0))); }; }; for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]); ttq.instance = function (t) { for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]); return e; }; ttq.load = function (e, n) { var r = 'https://analytics.tiktok.com/i18n/pixel/events.js'; ttq._i = ttq._i || {}; ttq._i[e] = []; ttq._i[e]._u = r; ttq._t = ttq._t || {}; ttq._t[e] = +new Date(); ttq._o = ttq._o || {}; ttq._o[e] = n || {}; script(r + '?sdkid=' + e + '&lib=' + t); }; ttq.load(C.tiktok); ttq.page(); }(w, d, 'ttq');
    }
    if (C.clarity) {
        w.clarity = w.clarity || function () { (w.clarity.q = w.clarity.q || []).push(arguments); };
        script('https://www.clarity.ms/tag/' + encodeURIComponent(C.clarity));
    }

    /* ---------- Eventos de la compra ---------- */
    function evento(e) {
        var nombre = e && e[0], x = (e && e[1]) || {}, valor = Number(x.valor) || 0, moneda = x.moneda || 'COP';
        if (nombre === 'inicio_compra') {
            if (w.fbq) w.fbq('track', 'InitiateCheckout', {value: valor, currency: moneda, content_type: 'product'});
            if (w.gtag) w.gtag('event', 'begin_checkout', {value: valor, currency: moneda});
            if (w.ttq) w.ttq.track('InitiateCheckout', {value: valor, currency: moneda, content_type: 'product'});
            if (C.gtm) w.dataLayer.push({event: 'begin_checkout', ecommerce: {value: valor, currency: moneda}});
        } else if (nombre === 'compra' && x.id) {
            // Una sola vez por pedido aunque la página del pedido se abra de nuevo
            var marca = 'dt_compra_' + x.id;
            try { if (w.localStorage.getItem(marca)) return; w.localStorage.setItem(marca, '1'); } catch (er) {}
            if (w.fbq) w.fbq('track', 'Purchase', {value: valor, currency: moneda, content_type: 'product'}, {eventID: x.id});
            if (w.gtag) {
                if (C.ga4) w.gtag('event', 'purchase', {transaction_id: x.id, value: valor, currency: moneda});
                if (C.gadsCompra) w.gtag('event', 'conversion', {send_to: C.gadsCompra, value: valor, currency: moneda, transaction_id: x.id});
            }
            if (w.ttq) w.ttq.track('CompletePayment', {value: valor, currency: moneda, content_type: 'product', content_id: x.id});
            if (C.gtm) w.dataLayer.push({event: 'purchase', ecommerce: {transaction_id: x.id, value: valor, currency: moneda}});
        }
    }
    var cola = Array.isArray(w.dtq) ? w.dtq : [];
    w.dtq = {push: evento};
    cola.forEach(evento);
})(window, document);
