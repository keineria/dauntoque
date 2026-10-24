<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Lo que decide el dueño en Califícame: sello y botón en el perfil, pedir tras cada cita y su enlace de Google. */
class CalificameAjuste extends Model
{
    protected $table = 'calificame_ajustes';

    protected $fillable = ['user_id', 'mostrar_sello', 'mostrar_boton', 'pedir_tras_cita', 'google_url'];

    protected $casts = ['mostrar_sello' => 'boolean', 'mostrar_boton' => 'boolean', 'pedir_tras_cita' => 'boolean'];

    public static function de(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId], ['mostrar_sello' => true, 'mostrar_boton' => true, 'pedir_tras_cita' => true]);
    }
}
