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
        'zelle', 'cashapp', 'venmo', 'redes_sociales', 'galeria', 'nfc_uid', 'activo', 'activo_en',
        'foto_perfil', 'foto_portada', 'titulo_galeria' // <-- Archivos nuevos autorizados
    ];

    // zelle, cashapp y venmo ya no se usan: quedan de respaldo de payment_methods (ver config/pagos.php)
    protected $casts = [
        'redes_sociales' => 'array',
        'galeria' => 'array',
        'payment_methods' => 'array',
        'activo_en' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Orden del "perfil activo": primero el que el usuario fijó como activo (activo_en, solo lo cambia
     * ProfileController::activar) y, si ninguno lo está, el primero que creó.
     * Es la regla que decide qué tarjeta se ve en portal.dauntoque.com/{nickname}.
     */
    public function scopeOrdenActivo(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN activo_en IS NULL THEN 1 ELSE 0 END')
            ->orderBy('activo_en', 'desc')
            ->orderBy('id', 'asc');
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
