<?php

/*
 * Textos del sitio de ventas por idioma. :precio, :antes, :ahorro y :pagos se reemplazan
 * con los datos del país de cada versión (tabla prices y config/pagos.php).
 */
return [
    'es' => [
        'lang' => 'es',
        'marca' => 'DaUnToque',
        'nav' => ['inicio' => 'Inicio', 'tarjetas' => 'Tarjetas', 'preguntas' => 'Preguntas', 'comprar' => 'Quiero mi tarjeta', 'pais' => 'País e idioma'],
        'versiones' => ['es-co' => 'Colombia · Español', 'es-us' => 'EE. UU. · Español', 'en-us' => 'USA · English'],
        'wa' => 'Hola, quiero mi tarjeta DaUnToque',
        'waAcabado' => 'Hola, quiero mi tarjeta DaUnToque :acabado (:precio)',
        'waDuda' => 'Hola, tengo una pregunta sobre la tarjeta DaUnToque',
        'escribenos' => 'Escríbenos por WhatsApp',

        'inicio' => [
            'titulo' => 'Tarjeta de presentación NFC premium | DaUnToque',
            'descripcion' => 'Un toque y tu contacto queda guardado: perfil web, WhatsApp, portafolio y cobros con :pagos. Pago único de :precio, sin mensualidades.',
            'oferta' => 'Precio de lanzamiento · Ahorras :ahorro',
            'h1a' => 'Deja de repartir papel.',
            'h1b' => 'Empieza a cerrar tratos.',
            'sub' => 'Acercas tu tarjeta al celular de tu cliente y se abre tu perfil: guarda tu contacto, te escribe por WhatsApp y te paga en el acto. Sin apps.',
            'cta' => 'Quiero mi tarjeta',
            'unico' => 'Pago único · Sin mensualidades',
            'compatible' => 'Funciona con',
            'compatibles' => ['iPhone', 'Android con NFC'],
            'videoAlt' => 'Un profesional acerca su tarjeta DaUnToque al celular de un cliente y se abre su perfil',

            'toqueH' => 'Un toque. Tu contacto guardado.',
            'toqueP' => 'Así se ve lo que recibe tu cliente: tu perfil abierto en su celular, listo para guardarte, escribirte o pagarte.',
            'pasos' => ['Acercas la tarjeta', 'Se abre tu perfil', 'Te guarda o te paga'],
            'visorH' => 'Mira perfiles reales',
            'visorP' => 'Toca un perfil para verlo en vivo, tal como lo ve tu cliente.',
            'visorAbrir' => 'Abrir en otra pestaña',

            'beneficiosH' => 'Diseñada para cerrar ventas',
            'beneficios' => [
                ['Contacto al instante', 'Tu cliente te guarda en su agenda con un toque, sin errores de tipeo.'],
                ['WhatsApp directo', 'Un botón y ya te está escribiendo, con tu número correcto.'],
                ['Cobra en el acto', 'Botones de :pagos con tu dato listo para copiar.'],
                ['Tu vitrina', 'Fotos de tu trabajo y tus redes en un solo perfil.'],
                ['Sabes quién te vio', 'Visitas, contactos guardados y clics en tu panel.'],
                ['Cero aplicaciones', 'Funciona con el NFC del celular, nada que instalar.'],
            ],

            'acabadosH' => 'Minimalismo que impone respeto',
            'acabadosP' => 'Sin logos saturados ni diseños ruidosos. El material es el mensaje.',

            'casosH' => 'Resultados reales',
            'casosP' => 'Profesionales que ya cambiaron el papel por un toque.',
            'verPerfil' => 'Ver su perfil',

            'comparaH' => 'Papel, QR o NFC',
            'comparaCols' => ['', 'Papel', 'Código QR', 'DaUnToque NFC'],
            'comparaFilas' => [
                ['Se guarda en la agenda', 'No', 'A veces', 'Sí, con un toque'],
                ['WhatsApp y redes', 'No', 'Sí', 'Sí'],
                ['Cobrar en el momento', 'No', 'No', 'Sí'],
                ['Se actualiza sin reimprimir', 'No', 'Depende', 'Sí'],
                ['Imagen que proyecta', 'Común', 'Común', 'Premium'],
                ['Costo', 'Cada pedido', 'Bajo', 'Un solo pago'],
            ],
            'calcH' => '¿Cuánto gastas en papel?',
            'calcTarjetas' => 'Tarjetas que repartes al mes',
            'calcCosto' => 'Costo de 100 tarjetas de papel',
            'calcAnio' => 'En papel gastas al año',
            'calcVs' => 'DaUnToque, una sola vez',

            'comoH' => 'Cómo funciona',
            'como' => [
                ['Pides tu tarjeta', 'Eliges Matte Black o Crystal y pagas con :pagos.'],
                ['Creas tu perfil', 'Lo armas en minutos en el portal: foto, WhatsApp, fotos y cobros.'],
                ['Tocas y cierras', 'Acercas la tarjeta al celular de tu cliente y listo.'],
            ],

            'precioH' => 'Da un toque. Potencia tu negocio.',
            'precioP' => 'Una inversión única que cambia cómo te perciben tus clientes.',
            'producto' => 'Tarjeta DaUnToque',
            'productoSub' => 'Elige Matte Black o Crystal al hacer tu pedido.',
            'incluye' => [
                'Tarjeta física con chip NFC NTAG215',
                'Perfil web con tu foto, WhatsApp, redes y portafolio',
                'Botones de cobro con :pagos',
                'Panel para editar tu perfil cuando quieras',
                'Analíticas: visitas, contactos guardados y clics',
            ],
            'ctaPrecio' => 'Quiero cerrar más tratos',

            'faqH' => 'Preguntas frecuentes',
            'faqTodas' => 'Ver todas las preguntas',
            'cierreH' => 'Tu próxima reunión empieza con un toque.',
        ],

        'acabados' => [
            'matte-black' => ['nombre' => 'Matte Black', 'corto' => 'Para el que lidera la mesa.', 'texto' => 'Negro mate, sobrio y con autoridad. Se siente sólida en la mano y no deja huellas.', 'titulo' => 'Tarjeta NFC Matte Black | DaUnToque', 'video' => 'tienda'],
            'crystal' => ['nombre' => 'Crystal', 'corto' => 'El rompehielos que nadie olvida.', 'texto' => 'Transparente y moderna. Llama la atención apenas la sacas y abre la conversación sola.', 'titulo' => 'Tarjeta NFC Crystal | DaUnToque', 'video' => 'florista'],
        ],

        'tarjeta' => [
            'descripcion' => 'Tarjeta :acabado con chip NFC y perfil web incluido. Guarda tu contacto, recibe WhatsApp y cobra con :pagos. Pago único de :precio.',
            'migas' => 'Tarjetas',
            'incluyeH' => 'Qué incluye',
            'detallesH' => 'Detalles',
            'detalles' => [
                ['Chip', 'NFC NTAG215'],
                ['Funciona con', 'iPhone y Android con NFC, sin apps'],
                ['Perfil web', 'Incluido, lo editas cuando quieras'],
                ['Pago', 'Único, sin mensualidades'],
            ],
            'otro' => 'Ver también',
            'cta' => 'Quiero la :acabado',
        ],

        'preguntas' => [
            'titulo' => 'Preguntas frecuentes | DaUnToque',
            'descripcion' => 'Todo sobre la tarjeta DaUnToque: cómo funciona, compatibilidad, pagos con :pagos, perfil web y pedidos. Pago único de :precio.',
            'h1' => 'Preguntas frecuentes',
            'sub' => '¿No encuentras tu respuesta? Escríbenos y te ayudamos.',
        ],

        'faq' => [
            ['¿Necesita una aplicación?', 'No. Tu cliente solo acerca su celular a la tarjeta y tu perfil se abre en el navegador. No tiene que instalar nada.'],
            ['¿Con qué celulares funciona?', 'Con iPhone y con los Android que tienen NFC, que son la gran mayoría. Si un celular no tiene NFC, puedes compartir tu enlace por WhatsApp o redes.'],
            ['¿Hay que pagar mensualidad?', 'No. Pagas una sola vez :precio y la tarjeta y tu perfil son tuyos.'],
            ['¿Puedo cambiar mi perfil después?', 'Sí. Desde tu panel cambias foto, datos, fotos de trabajos, colores y medios de pago cuando quieras, sin cambiar la tarjeta.'],
            ['¿Cómo me pagan mis clientes?', 'Tu perfil muestra botones de :pagos. Tu cliente toca, copia tu dato o abre su app y te paga en el momento.'],
            ['¿Cómo pago mi tarjeta?', 'Con :pagos. Al hacer tu pedido te damos el dato para pagar y nos envías el comprobante.'],
            ['¿Qué diferencia hay entre Matte Black y Crystal?', 'Solo el acabado: Matte Black es negro mate y sobrio; Crystal es transparente y llamativo. Las dos traen el mismo chip y el mismo perfil.'],
            ['¿Cuánto tarda en llegar?', 'Te confirmamos el tiempo de entrega a tu ciudad por WhatsApp cuando haces tu pedido.'],
            ['¿Puedo ver cuántas personas vieron mi perfil?', 'Sí. Tu panel muestra visitas, contactos guardados y clics en WhatsApp, llamadas y pagos.'],
        ],

        'pie' => ['lema' => 'Tarjetas de presentación inteligentes.', 'derechos' => 'Todos los derechos reservados.', 'portal' => 'Entrar a mi panel'],
        'raiz' => ['titulo' => 'DaUnToque · Elige tu país', 'h1' => 'Elige tu país', 'cargando' => 'Te llevamos a tu versión…'],
    ],

    'en' => [
        'lang' => 'en',
        'marca' => 'DaUnToque',
        'nav' => ['inicio' => 'Home', 'tarjetas' => 'Cards', 'preguntas' => 'FAQ', 'comprar' => 'Get my card', 'pais' => 'Country & language'],
        'versiones' => ['es-co' => 'Colombia · Español', 'es-us' => 'EE. UU. · Español', 'en-us' => 'USA · English'],
        'wa' => 'Hi, I want my DaUnToque card',
        'waAcabado' => 'Hi, I want my DaUnToque :acabado card (:precio)',
        'waDuda' => 'Hi, I have a question about the DaUnToque card',
        'escribenos' => 'Message us on WhatsApp',

        'inicio' => [
            'titulo' => 'Premium NFC business card | DaUnToque',
            'descripcion' => 'One tap and your contact is saved: web profile, WhatsApp, portfolio and payments with :pagos. One-time :precio, no monthly fees.',
            'oferta' => 'Launch price · Save :ahorro',
            'h1a' => 'Stop handing out paper.',
            'h1b' => 'Start closing deals.',
            'sub' => 'Tap your card on your client\'s phone and your profile opens: they save your contact, text you on WhatsApp and pay you on the spot. No apps.',
            'cta' => 'Get my card',
            'unico' => 'One-time payment · No monthly fees',
            'compatible' => 'Works with',
            'compatibles' => ['iPhone', 'Android with NFC'],
            'videoAlt' => 'A professional taps a DaUnToque card on a client\'s phone and the profile opens',

            'toqueH' => 'One tap. Contact saved.',
            'toqueP' => 'This is what your client gets: your profile open on their phone, ready to save you, message you or pay you.',
            'pasos' => ['Tap the card', 'Your profile opens', 'They save or pay you'],
            'visorH' => 'See real profiles',
            'visorP' => 'Tap a profile to see it live, exactly as your client sees it.',
            'visorAbrir' => 'Open in a new tab',

            'beneficiosH' => 'Built to close sales',
            'beneficios' => [
                ['Instant contact', 'Your client saves you to their contacts with one tap, no typos.'],
                ['Direct WhatsApp', 'One button and they are already texting you.'],
                ['Get paid on the spot', ':pagos buttons with your details ready to copy.'],
                ['Your showcase', 'Photos of your work and your socials in one profile.'],
                ['Know who saw you', 'Views, saved contacts and clicks in your dashboard.'],
                ['Zero apps', 'Works with the phone\'s NFC, nothing to install.'],
            ],

            'acabadosH' => 'Minimalism that commands respect',
            'acabadosP' => 'No loud logos, no busy designs. The material is the message.',

            'casosH' => 'Real results',
            'casosP' => 'Professionals who already swapped paper for a tap.',
            'verPerfil' => 'See their profile',

            'comparaH' => 'Paper, QR or NFC',
            'comparaCols' => ['', 'Paper', 'QR code', 'DaUnToque NFC'],
            'comparaFilas' => [
                ['Saved to contacts', 'No', 'Sometimes', 'Yes, with one tap'],
                ['WhatsApp and socials', 'No', 'Yes', 'Yes'],
                ['Get paid on the spot', 'No', 'No', 'Yes'],
                ['Update without reprinting', 'No', 'Depends', 'Yes'],
                ['Impression it makes', 'Ordinary', 'Ordinary', 'Premium'],
                ['Cost', 'Every order', 'Low', 'One payment'],
            ],
            'calcH' => 'How much do you spend on paper?',
            'calcTarjetas' => 'Cards you hand out per month',
            'calcCosto' => 'Cost of 100 paper cards',
            'calcAnio' => 'Paper cards per year',
            'calcVs' => 'DaUnToque, one time',

            'comoH' => 'How it works',
            'como' => [
                ['Order your card', 'Pick Matte Black or Crystal and pay with :pagos.'],
                ['Build your profile', 'Set it up in minutes: photo, WhatsApp, work photos and payments.'],
                ['Tap and close', 'Tap the card on your client\'s phone. Done.'],
            ],

            'precioH' => 'One tap. A stronger business.',
            'precioP' => 'A one-time investment that changes how clients see you.',
            'producto' => 'DaUnToque card',
            'productoSub' => 'Choose Matte Black or Crystal when you order.',
            'incluye' => [
                'Physical card with NFC NTAG215 chip',
                'Web profile with your photo, WhatsApp, socials and portfolio',
                'Payment buttons for :pagos',
                'Dashboard to edit your profile anytime',
                'Analytics: views, saved contacts and clicks',
            ],
            'ctaPrecio' => 'I want to close more deals',

            'faqH' => 'Frequently asked questions',
            'faqTodas' => 'See all questions',
            'cierreH' => 'Your next meeting starts with a tap.',
        ],

        'acabados' => [
            'matte-black' => ['nombre' => 'Matte Black', 'corto' => 'For the one who leads the table.', 'texto' => 'Matte black, understated and authoritative. Solid in the hand and fingerprint-free.', 'titulo' => 'Matte Black NFC card | DaUnToque', 'video' => 'tienda'],
            'crystal' => ['nombre' => 'Crystal', 'corto' => 'The icebreaker nobody forgets.', 'texto' => 'Transparent and modern. It turns heads the moment you take it out and starts the conversation for you.', 'titulo' => 'Crystal NFC card | DaUnToque', 'video' => 'florista'],
        ],

        'tarjeta' => [
            'descripcion' => ':acabado card with NFC chip and web profile included. Save your contact, get WhatsApp messages and get paid with :pagos. One-time :precio.',
            'migas' => 'Cards',
            'incluyeH' => 'What\'s included',
            'detallesH' => 'Details',
            'detalles' => [
                ['Chip', 'NFC NTAG215'],
                ['Works with', 'iPhone and Android with NFC, no apps'],
                ['Web profile', 'Included, edit it anytime'],
                ['Payment', 'One-time, no monthly fees'],
            ],
            'otro' => 'See also',
            'cta' => 'Get the :acabado',
        ],

        'preguntas' => [
            'titulo' => 'Frequently asked questions | DaUnToque',
            'descripcion' => 'Everything about the DaUnToque card: how it works, compatibility, payments with :pagos, web profile and orders. One-time :precio.',
            'h1' => 'Frequently asked questions',
            'sub' => 'Can\'t find your answer? Message us and we\'ll help.',
        ],

        'faq' => [
            ['Do my clients need an app?', 'No. They just hold their phone near the card and your profile opens in the browser. Nothing to install.'],
            ['Which phones does it work with?', 'iPhone and Android phones with NFC, which is most of them. If a phone has no NFC, you can share your link by WhatsApp or social media.'],
            ['Is there a monthly fee?', 'No. You pay :precio once and the card and your profile are yours.'],
            ['Can I change my profile later?', 'Yes. From your dashboard you can change your photo, details, work photos, colors and payment methods anytime, without changing the card.'],
            ['How do my clients pay me?', 'Your profile shows :pagos buttons. Your client taps, copies your details or opens their app and pays you right away.'],
            ['How do I pay for my card?', 'With :pagos. When you order we send you the payment details and you send us the receipt.'],
            ['What is the difference between Matte Black and Crystal?', 'Only the finish: Matte Black is understated matte black; Crystal is transparent and eye-catching. Both have the same chip and profile.'],
            ['How long does shipping take?', 'We confirm the delivery time to your city on WhatsApp when you place your order.'],
            ['Can I see how many people viewed my profile?', 'Yes. Your dashboard shows views, saved contacts and clicks on WhatsApp, calls and payments.'],
        ],

        'pie' => ['lema' => 'Smart business cards.', 'derechos' => 'All rights reserved.', 'portal' => 'Log in to my dashboard'],
        'raiz' => ['titulo' => 'DaUnToque · Choose your country', 'h1' => 'Choose your country', 'cargando' => 'Taking you to your version…'],
    ],
];
