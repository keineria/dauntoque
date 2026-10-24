<?php

// App adicional "Califícame": el cliente califica la atención de 1 a 5 estrellas. Reglas en un solo lugar.
return [
    // El primer año va gratis con la tarjeta (el precio de la renovación lo define DaUnToque más adelante)
    'dias_gratis' => 365,

    // El sello "4,9 ★ · 37 calificaciones" sale en el perfil desde este número de calificaciones
    'minimo_sello' => 3,

    // Comentarios que el dueño puede elegir para mostrar en su perfil (de 4 y 5 estrellas)
    'maximo_comentarios' => 6,

    // Desde estas estrellas la calificación cuenta como buena: se puede mostrar y se invita a reseñar en Google.
    // Por debajo, le llega en privado al dueño y nunca sale en el perfil
    'estrellas_buenas' => 4,

    // Pedir la calificación por correo después de una cita de la Agenda: horas después de terminar y hasta cuántos días
    'pedir_tras_horas' => 1,
    'pedir_hasta_dias' => 3,
];
