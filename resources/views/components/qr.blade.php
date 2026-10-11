{{--
    Código QR de un enlace, con descarga en PNG (para compartir) y SVG (para imprenta).
    Se genera en el navegador. url: enlace fijo; o modelo: expresión de Alpine que da el enlace.
--}}
@props(['url' => null, 'modelo' => null, 'nombre' => 'qr-dauntoque', 'tam' => 168])
@php($expr = $modelo ?? Js::from($url))
@once
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js" integrity="sha512-ZDSPMa/JM1D+7kdg2x3BsruQ6T/JpJo3jWDWkCZsP+5yVyp1KfESqLI+7RqB5k24F7p2cV7i2YHh/890y6P6Sw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        window.dtQr = {
            crear(url) { const qr = qrcode(0, 'M'); qr.addData(url); qr.make(); return qr; },
            svg(url) { return url ? this.crear(url).createSvgTag({ cellSize: 8, margin: 32, scalable: true }) : ''; },
            descargar(href, archivo) { const a = document.createElement('a'); a.href = href; a.download = archivo; a.click(); },
            // 1024 px o más, con borde blanco de 4 módulos para que cualquier cámara lo lea
            png(url, nombre) {
                const qr = this.crear(url), n = qr.getModuleCount(), margen = 4, celda = Math.ceil(1024 / (n + margen * 2));
                const lado = celda * (n + margen * 2), lienzo = document.createElement('canvas');
                lienzo.width = lienzo.height = lado;
                const ctx = lienzo.getContext('2d');
                ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, lado, lado); ctx.fillStyle = '#000';
                for (let f = 0; f < n; f++) for (let c = 0; c < n; c++) if (qr.isDark(f, c)) ctx.fillRect((c + margen) * celda, (f + margen) * celda, celda, celda);
                this.descargar(lienzo.toDataURL('image/png'), nombre + '.png');
            },
            svgArchivo(url, nombre) {
                const enlace = URL.createObjectURL(new Blob([this.svg(url)], { type: 'image/svg+xml' }));
                this.descargar(enlace, nombre + '.svg');
                setTimeout(() => URL.revokeObjectURL(enlace), 1000);
            },
        };
    </script>
@endonce
<div x-data {{ $attributes }}>
    <div x-html="dtQr.svg({{ $expr }})" role="img" aria-label="Código QR" style="width:{{ $tam }}px;height:{{ $tam }}px;background:#fff;border-radius:12px;overflow:hidden;line-height:0"></div>
    <div style="display:flex;gap:6px;margin-top:10px">
        <button type="button" class="dt-btn" @click="dtQr.png({{ $expr }}, {{ Js::from($nombre) }})">Descargar PNG</button>
        <button type="button" class="dt-btn" @click="dtQr.svgArchivo({{ $expr }}, {{ Js::from($nombre) }})" title="Para imprenta: se ve nítido a cualquier tamaño">SVG</button>
    </div>
</div>
