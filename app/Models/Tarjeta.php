<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/** Tarjeta física (chip NFC) y su servicio anual, que empieza al activarla. */
class Tarjeta extends Model
{
    protected $fillable = ['user_id', 'nfc_uid', 'activada_en', 'vence_en'];

    protected $casts = [
        'activada_en' => 'datetime',
        'vence_en' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registra la primera activación del chip y arranca su año de servicio.
     * Si el chip ya estaba registrado (se mudó de perfil), conserva sus fechas.
     */
    public static function registrarActivacion(int $userId, string $nfcUid): self
    {
        return static::firstOrCreate(['nfc_uid' => $nfcUid], [
            'user_id' => $userId,
            'activada_en' => now(),
            'vence_en' => now()->addDays(config('servicio.dias')),
        ]);
    }

    /**
     * Estado del servicio para el panel.
     * activo -> pronto (últimos aviso_dias) -> gracia (vencido, aún visible) -> pausa.
     */
    public function servicio(?CarbonInterface $hoy = null): array
    {
        $hoy = ($hoy ?? now())->copy()->startOfDay();
        $total = max(1, (int) $this->activada_en->copy()->startOfDay()->diffInDays($this->vence_en->copy()->startOfDay()));
        $restantes = (int) $hoy->diffInDays($this->vence_en->copy()->startOfDay(), false);
        $gracia = (int) config('servicio.gracia_dias');

        $estado = match (true) {
            $restantes > config('servicio.aviso_dias') => 'activo',
            $restantes > 0 => 'pronto',
            $restantes > -$gracia => 'gracia',
            default => 'pausa',
        };

        return [
            'estado' => $estado,
            'diasRestantes' => max(0, $restantes),
            'diasGracia' => $estado === 'gracia' ? $gracia + $restantes : 0,
            'porcentaje' => round(min(1, max(0, $restantes / $total)) * 100, 1),
            'activadaEn' => $this->activada_en,
            'venceEn' => $this->vence_en,
        ];
    }
}
