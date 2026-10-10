<script>
    // Reduce una foto del celular antes de enviarla (como la foto de perfil): lado mayor de 1280 px, JPEG al 82 %.
    // Así varias fotos de 5-10 MB no superan el límite del servidor. Devuelve una promesa con el dataURL.
    function dtReducirImagen(archivo, ladoMax = 1280, calidad = 0.82) {
        return new Promise((resolver, rechazar) => {
            const url = URL.createObjectURL(archivo);
            const img = new Image();
            img.onload = () => {
                const escala = Math.min(1, ladoMax / Math.max(img.naturalWidth, img.naturalHeight));
                const lienzo = document.createElement('canvas');
                lienzo.width = Math.round(img.naturalWidth * escala);
                lienzo.height = Math.round(img.naturalHeight * escala);
                const ctx = lienzo.getContext('2d');
                ctx.fillStyle = '#ffffff'; // fondo blanco para los PNG con transparencia
                ctx.fillRect(0, 0, lienzo.width, lienzo.height);
                ctx.drawImage(img, 0, 0, lienzo.width, lienzo.height);
                URL.revokeObjectURL(url);
                resolver(lienzo.toDataURL('image/jpeg', calidad));
            };
            img.onerror = () => { URL.revokeObjectURL(url); rechazar(new Error('imagen')); };
            img.src = url;
        });
    }
</script>
