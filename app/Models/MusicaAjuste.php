<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Lo que decide el dueño en la app Música: título, canción destacada, lista de Spotify y si se ve en el perfil. */
class MusicaAjuste extends Model
{
    protected $table = 'musica_ajustes';

    protected $fillable = ['user_id', 'titulo', 'cancion', 'lista', 'mostrar'];

    protected $casts = ['mostrar' => 'boolean'];

    public static function de(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId], ['mostrar' => true]);
    }
}
