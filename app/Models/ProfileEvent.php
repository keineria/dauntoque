<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['profile_id', 'accion', 'visitante'];

    /** Acciones que el perfil público puede registrar. */
    public static function accionesValidas(): array
    {
        $redes = array_map(fn ($id) => 'red_social_' . $id, array_keys(config('redes', [])));

        // Los clics en pagos se registran con el tipo del medio (nequi, llave_breb, zelle, cashapp, venmo)
        $pagos = array_keys(config('pagos.medios', []));

        return array_merge(['vista_perfil', 'descarga_vcf', 'llamada', 'whatsapp', 'correo', 'compartir'], $pagos, $redes);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
