<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** App adicional de un usuario (por ahora solo "agenda") y hasta cuándo está paga. */
class AppAdicional extends Model
{
    protected $table = 'apps_adicionales';

    protected $fillable = ['user_id', 'app', 'activada_en', 'vence_en'];

    protected $casts = ['activada_en' => 'datetime', 'vence_en' => 'datetime'];

    public static function de(int $userId, string $app): ?self
    {
        return static::where('user_id', $userId)->where('app', $app)->first();
    }

    /** Activada y dentro del año pago (o del año gratis). */
    public function vigente(): bool
    {
        return $this->vence_en !== null && $this->vence_en->isFuture();
    }

    /** Días que le quedan (0 si ya venció). */
    public function diasRestantes(): int
    {
        return $this->vigente() ? (int) ceil(now()->diffInHours($this->vence_en) / 24) : 0;
    }
}
