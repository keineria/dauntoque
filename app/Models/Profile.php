<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'nickname', 'tipo_perfil', 'nombre', 'profesion', 'bio',
        'plantilla', 'alineacion', 'telefono', 'correo_contacto', 'direccion', 'pais', 'payment_methods',
        'zelle', 'cashapp', 'venmo', 'redes_sociales', 'galeria', 'nfc_uid', 'activo',
        'foto_perfil', 'foto_portada', 'titulo_galeria' // <-- Archivos nuevos autorizados
    ];

    // zelle, cashapp y venmo ya no se usan: quedan de respaldo de payment_methods (ver config/pagos.php)
    protected $casts = [
        'redes_sociales' => 'array',
        'galeria' => 'array',
        'payment_methods' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Orden del "perfil activo": primero el que tiene chip NFC y luego el editado más recientemente.
     * Es la regla que decide qué tarjeta se ve en portal.dauntoque.com/{nickname}.
     */
    public function scopeOrdenActivo(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN nfc_uid IS NOT NULL AND nfc_uid != "" THEN 1 ELSE 0 END DESC')
            ->orderBy('updated_at', 'desc');
    }

    /** Perfil que se muestra en público para un usuario. */
    public static function activoDe(int $userId): ?self
    {
        return static::where('user_id', $userId)->ordenActivo()->first();
    }

    /** Primer perfil creado: su nickname es el enlace permanente ("link maestro") del usuario. */
    public static function maestroDe(int $userId): ?self
    {
        return static::where('user_id', $userId)->orderBy('id', 'asc')->first();
    }
}
