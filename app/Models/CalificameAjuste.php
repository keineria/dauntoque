<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Lo que decide el dueño en Califícame: sello y botón en el perfil, pedir tras cada cita y su negocio de Google. */
class CalificameAjuste extends Model
{
    protected $table = 'calificame_ajustes';

    protected $fillable = [
        'user_id', 'mostrar_sello', 'mostrar_boton', 'pedir_tras_cita', 'google_url', 'mostrar_google',
        'google_place_id', 'google_nombre', 'google_direccion', 'google_maps_url', 'google_rating', 'google_total', 'google_resenas', 'google_actualizado_en',
    ];

    protected $casts = [
        'mostrar_sello' => 'boolean', 'mostrar_boton' => 'boolean', 'pedir_tras_cita' => 'boolean', 'mostrar_google' => 'boolean',
        'google_rating' => 'float', 'google_total' => 'integer', 'google_resenas' => 'array', 'google_actualizado_en' => 'datetime',
    ];

    public static function de(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId], ['mostrar_sello' => true, 'mostrar_boton' => true, 'pedir_tras_cita' => true, 'mostrar_google' => true]);
    }

    /** Conectó su negocio de Google y ya tenemos su calificación. */
    public function googleConectado(): bool
    {
        return filled($this->google_place_id);
    }
}
