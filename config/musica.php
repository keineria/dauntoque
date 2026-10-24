<?php

// App adicional "Música": el dueño (por ejemplo un DJ) pone en su perfil una canción destacada y una lista de Spotify.
return [
    // El primer año va gratis con la tarjeta; luego se renueva al año (decidido por Keiner, 2026-10-24)
    'dias_gratis' => 365,
    'precio_renovacion' => ['CO' => 12000, 'US' => 12],

    // Lo que se puede poner como lista: playlist, álbum, artista o pódcast de Spotify
    'tipos_lista' => ['playlist', 'album', 'artist', 'show'],

    // Alto del reproductor oficial de Spotify: compacto para la canción y grande para la lista
    'alto_cancion' => 152,
    'alto_lista' => 352,
];
