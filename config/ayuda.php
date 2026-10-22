<?php

/*
 * Preguntas frecuentes de Ayuda, por temas. Las respuestas son HTML propio (no lo escribe el cliente).
 * 'imagen' es una ilustración de resources/views/ayuda/ilustraciones.
 */
return [
    'temas' => [
        'empezar' => [
            'titulo' => 'Primeros pasos',
            'icono' => 'M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z',
            'preguntas' => [
                [
                    'p' => '¿Qué es DaUnToque y cómo funciona?',
                    'r' => '<p>DaUnToque es tu tarjeta de presentación inteligente. La tarjeta física tiene un chip NFC que, al acercarla a un celular, abre tu <b>perfil digital</b>: tu foto, tus datos, tus redes, tus medios de pago y el botón para guardar tu contacto.</p><p>Tu perfil también tiene un enlace propio (dauntoque.com/tuusuario) y un código QR, así que puedes compartirlo aunque no tengas la tarjeta a la mano.</p>',
                ],
                [
                    'p' => '¿Cómo creo mi cuenta y mi perfil?',
                    'r' => '<ol><li>Entra con el enlace del correo de tu compra o en <b>portal.dauntoque.com/register</b>. Usa el mismo correo con el que compraste.</li><li>En <b>Inicio</b> toca <b>Crear mi perfil</b>.</li><li>Elige tu usuario: es el final de tu enlace (dauntoque.com/<b>tuusuario</b>).</li><li>Sube tu foto, escribe tus datos, elige diseño, colores y tipo de letra, y guarda.</li></ol><p>Puedes crear tu perfil antes de que te llegue la tarjeta: el día que llegue solo la activas.</p>',
                ],
                [
                    'p' => '¿Cuántos perfiles puedo tener?',
                    'r' => '<p>Puedes tener hasta <b>2 perfiles</b> en tu cuenta, por ejemplo uno de trabajo y otro personal. Tu tarjeta y tu enlace muestran el que tengas como <b>perfil activo</b>, y lo cambias cuando quieras en <b>Mis perfiles</b>.</p>',
                    'imagen' => 'perfil-activo',
                ],
                [
                    'p' => '¿Puedo usar mi perfil sin la tarjeta?',
                    'r' => '<p>Sí. Comparte tu enlace por WhatsApp o redes, o muestra tu <b>código QR</b> (está en <b>Inicio</b> y lo puedes descargar para imprimirlo en tu local, tu factura o una presentación).</p>',
                ],
            ],
        ],

        'tarjeta' => [
            'titulo' => 'Tu tarjeta NFC',
            'icono' => 'M3 6h18v12H3zM7 10h4M7 14h8',
            'preguntas' => [
                [
                    'p' => '¿Cómo activo mi tarjeta?',
                    'r' => '<p>Tienes dos formas, y las dos dejan la tarjeta enlazada a tu perfil activo:</p><ol><li><b>Acercándola a tu celular:</b> desbloquea el celular, acerca la tarjeta a la parte de atrás y abre el enlace que aparece. Si no has iniciado sesión, el portal te pide entrar o crear tu cuenta y la tarjeta se enlaza sola.</li><li><b>Con el código de activación:</b> en tu panel, ve a <b>Inicio → Activa tu tarjeta</b>, escribe el código y toca <b>Activar</b>. Sirve desde un computador.</li></ol><p>Cuando queda activa ves el mensaje <b>¡Tarjeta activada!</b> y en Inicio aparece la ficha <b>Servicio de tu tarjeta</b>.</p>',
                    'imagen' => 'acercar',
                ],
                [
                    'p' => '¿Dónde encuentro mi código de activación?',
                    'r' => '<p>Te llega en el correo de tu compra, en el recuadro verde <b>Tu código de activación</b> (por ejemplo <code>k7mq-2xpa</code>). También aparece en la página de tu pedido.</p><p>Al escribirlo no importan las mayúsculas, los espacios ni el guion. Si no lo encuentras, revisa la carpeta de spam o escríbenos abajo y te lo reenviamos.</p>',
                    'imagen' => 'codigo',
                ],
                [
                    'p' => 'Mi celular no lee la tarjeta, ¿qué hago?',
                    'r' => '<ul><li><b>Android:</b> activa el NFC en <i>Ajustes → Conexiones → NFC</i> (o desliza el menú rápido y toca NFC). Acerca la tarjeta al centro de la parte de atrás.</li><li><b>iPhone (XR o más nuevo):</b> no necesitas nada; desbloquéalo y acerca la tarjeta al borde de arriba, junto a la cámara.</li><li>Quita las fundas gruesas o con metal, y deja la tarjeta quieta 1 o 2 segundos.</li><li>Si aun así no lee, usa el código de activación de tu correo o el código QR de tu perfil.</li></ul>',
                    'imagen' => 'antena',
                ],
                [
                    'p' => 'Acerqué la tarjeta y dice que no está habilitada',
                    'r' => '<p>Solo se activan las tarjetas registradas por DaUnToque. Si ves ese mensaje, escríbenos abajo con una foto de la tarjeta y el número de tu pedido y la habilitamos.</p>',
                ],
                [
                    'p' => '¿Cómo cambio el perfil que abre mi tarjeta?',
                    'r' => '<p>Ve a <b>Mis perfiles</b> y en el perfil que quieres mostrar toca el botón <b>Mostrar en público y en mi tarjeta</b>. Desde ese momento tu tarjeta, tu enlace y tu QR abren ese perfil; no hay que volver a grabar la tarjeta.</p>',
                    'imagen' => 'perfil-activo',
                ],
                [
                    'p' => 'Perdí mi tarjeta o se dañó',
                    'r' => '<p>Tu perfil sigue funcionando con tu enlace y tu QR. Escríbenos abajo para pedir una tarjeta nueva: cuando la actives, tu servicio y tus estadísticas siguen igual y la tarjeta anterior deja de estar enlazada a tu perfil.</p>',
                ],
            ],
        ],

        'perfil' => [
            'titulo' => 'Tu perfil',
            'icono' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21a8 8 0 0 1 16 0',
            'preguntas' => [
                [
                    'p' => '¿Cómo edito mis datos, mi foto o mi diseño?',
                    'r' => '<ul><li><b>Datos del perfil</b> (menú de la izquierda): nombre, cargo, biografía, foto, galería, contacto, redes y pagos.</li><li><b>Editar perfil</b> (en Inicio o en Mis perfiles): el editor paso a paso, con vista previa, para tu foto, especialidad, biografía, galería, redes, pagos y diseño (plantilla, colores y tipo de letra).</li></ul><p>Los cambios se ven en tu perfil apenas guardas.</p>',
                ],
                [
                    'p' => '¿Qué puedo poner en mi perfil?',
                    'r' => '<ul><li>Foto, nombre, cargo y biografía.</li><li>Teléfono, WhatsApp, correo y dirección.</li><li>Hasta 4 redes sociales.</li><li>Hasta 6 fotos en la galería (tus trabajos, productos o local).</li><li>Medios de pago para que te paguen con un toque (en Colombia, Nequi y llave Bre-B; en EE. UU., Zelle, Cash App y Venmo).</li><li>12 tipos de letra y los colores de tu marca.</li></ul>',
                ],
                [
                    'p' => '¿Cómo guardan mi contacto las personas?',
                    'r' => '<p>Tu perfil tiene el botón <b>Guardar contacto</b>. Al tocarlo, el celular de la otra persona abre su agenda con tu nombre, foto, teléfono y correo listos para guardar.</p>',
                ],
                [
                    'p' => '¿Puedo cambiar mi usuario (el final de mi enlace)?',
                    'r' => '<p>Tu usuario no se cambia desde el panel para que nunca se rompan los enlaces y QR que ya compartiste. Si de verdad lo necesitas, escríbenos abajo y lo revisamos contigo.</p>',
                ],
            ],
        ],

        'servicio' => [
            'titulo' => 'Servicio y renovación',
            'icono' => 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            'preguntas' => [
                [
                    'p' => '¿Tengo que pagar mensualidades?',
                    'r' => '<p>No. Pagas la tarjeta una vez y tu perfil funciona por un año. Después solo renuevas una vez al año el servidor de tu perfil.</p>',
                ],
                [
                    'p' => '¿Cuándo empieza y cuándo vence mi año de servicio?',
                    'r' => '<p>El año (365 días) empieza el día que <b>activas</b> tu tarjeta, no el día de la compra. En <b>Inicio</b>, la ficha <b>Servicio de tu tarjeta</b> te muestra cuántos días te quedan y la fecha de vencimiento.</p>',
                    'imagen' => 'servicio',
                ],
                [
                    'p' => '¿Cómo renuevo y cuánto cuesta?',
                    'r' => '<p>Cuando falten 30 días, en <b>Inicio</b> aparece el botón <b>Renovar ahora</b> con el precio. Pagas en línea con Bold y el año nuevo aparece en minutos. Si el pago en línea no está disponible, el botón te lleva a WhatsApp para renovar con un asesor.</p><p>El año nuevo se suma al final del actual, así que si renuevas antes no pierdes ningún día.</p>',
                ],
                [
                    'p' => '¿Qué pasa si se me vence?',
                    'r' => '<p>Te avisamos por correo un mes antes, la última semana y el día que vence. Después tienes <b>15 días de gracia</b> en los que tu perfil sigue funcionando. Si no renuevas, tu tarjeta queda en pausa; al renovar vuelve a funcionar al instante con todo lo que tenías.</p>',
                ],
            ],
        ],

        'compras' => [
            'titulo' => 'Compras y envíos',
            'icono' => 'M3 4h2l2.5 12h11L21 7H6M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2z',
            'preguntas' => [
                [
                    'p' => '¿Cómo pago mi tarjeta?',
                    'r' => '<p>En dauntoque.com eliges tu tarjeta, escribes tus datos de envío y pagas con <b>Bold</b>, con los medios que Bold te muestra (tarjeta, PSE y otros). Al confirmarse el pago te llega un correo con tu número de pedido, el enlace para crear tu perfil y tu código de activación.</p>',
                ],
                [
                    'p' => '¿Cuándo me llega la tarjeta?',
                    'r' => '<p>Preparamos tu tarjeta y te escribimos al celular que dejaste en la compra con la guía de envío. Mientras llega, crea tu perfil para que solo tengas que activarla.</p>',
                ],
                [
                    'p' => 'Mi pago fue rechazado',
                    'r' => '<p>Si Bold rechaza el pago no se hace ningún cobro. Puedes intentarlo de nuevo con otro medio de pago desde la página de tu pedido. Si te aparece un cobro sin confirmación, escríbenos abajo con el número de tu pedido.</p>',
                ],
                [
                    'p' => '¿Mi tarjeta tiene garantía?',
                    'r' => '<p>Sí, tienes <b>30 días de garantía</b>. Si tu tarjeta llega con un problema o deja de funcionar en ese tiempo, escríbenos abajo y lo resolvemos.</p>',
                ],
            ],
        ],

        'apps' => [
            'titulo' => 'Apps y analíticas',
            'icono' => 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM16.5 13v7M13 16.5h7',
            'preguntas' => [
                [
                    'p' => '¿Qué son las apps adicionales?',
                    'r' => '<p>Son funciones extra que puedes encender desde <b>Ver apps</b>. Cuando activas una, aparece en el menú en <b>Apps activas</b>.</p><p>La primera es la <b>Agenda de citas</b>: tus clientes reservan desde tu perfil, reciben confirmación y recordatorio por correo, y tú lo ves todo en tu panel. Es gratis el primer año; después cuesta $99.000 COP o $39 USD al año.</p>',
                ],
                [
                    'p' => '¿Qué me muestran las analíticas?',
                    'r' => '<p>En <b>Analíticas</b> ves cuántas veces abrieron tu perfil, cuántos guardaron tu contacto, cuántos te escribieron por WhatsApp y los clics en tus redes y en tus medios de pago. También ves las visitas de los últimos 14 días, contando una por persona y día.</p>',
                ],
            ],
        ],

        'cuenta' => [
            'titulo' => 'Cuenta y seguridad',
            'icono' => 'M6 10V8a6 6 0 1 1 12 0v2M5 10h14v11H5z',
            'preguntas' => [
                [
                    'p' => 'Olvidé mi contraseña',
                    'r' => '<p>En la pantalla de entrada toca <b>¿La olvidaste?</b>, escribe tu correo y te llega un enlace para crear una contraseña nueva.</p>',
                ],
                [
                    'p' => '¿Cómo cambio mi contraseña?',
                    'r' => '<p>Arriba a la derecha toca tu nombre y entra a <b>Mi cuenta y seguridad</b>. Escribe tu contraseña actual y la nueva dos veces.</p>',
                ],
                [
                    'p' => '¿Qué es la verificación en dos pasos?',
                    'r' => '<p>Es una capa extra de seguridad: además de tu contraseña, al entrar te pedimos un código de 6 números de una app autenticadora (Google Authenticator, Microsoft Authenticator u otra). Actívala en <b>Mi cuenta y seguridad</b> escaneando el QR. Guarda los 8 códigos de respaldo: sirven si pierdes el celular.</p>',
                ],
                [
                    'p' => '¿Puedo cambiar el correo de mi cuenta?',
                    'r' => '<p>Sí. Escríbenos abajo con el correo nuevo y lo cambiamos; desde ese momento entras con el correo nuevo y tu misma contraseña.</p>',
                ],
            ],
        ],
    ],
];
