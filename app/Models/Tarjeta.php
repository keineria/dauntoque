<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/** Tarjeta física (chip NFC) y su servicio anual, que empieza al activarla. */
class Tarjeta extends Model
{
    /** Avisos de renovación por correo, en orden; cada uno se manda una sola vez por año de servicio. */
    public const ETAPAS_AVISO = ['mes', 'semana', 'vencido', 'pausa'];

    protected $fillable = ['user_id', 'nfc_uid', 'activada_en', 'vence_en', 'aviso_etapa'];

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
     * Suma un año de servicio. Si aún no vencía, el año se suma al vencimiento (no se pierden días);
     * si ya venció, cuenta desde hoy.
     */
    public function renovar(): void
    {
        $desde = $this->vence_en->isFuture() ? $this->vence_en : now();
        $this->vence_en = $desde->copy()->addDays(config('servicio.dias'));
        $this->aviso_etapa = null;
        $this->save();
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

    /**
     * Aviso de renovación que toca mandar hoy, o null si no hay ninguno nuevo.
     * mes: entra en los últimos aviso_dias · semana: últimos 7 días · vencido: en gracia · pausa: terminó la gracia.
     * Si el cron estuvo apagado y se saltó etapas, solo manda la más reciente.
     */
    public function avisoPendiente(?CarbonInterface $hoy = null): ?string
    {
        $servicio = $this->servicio($hoy);
        $etapa = match ($servicio['estado']) {
            'pronto' => $servicio['diasRestantes'] > 7 ? 'mes' : 'semana',
            'gracia' => 'vencido',
            'pausa' => 'pausa',
            default => null,
        };
        if ($etapa === null) {
            return null;
        }
        $enviada = array_search($this->aviso_etapa, self::ETAPAS_AVISO, true);

        return $enviada === false || array_search($etapa, self::ETAPAS_AVISO, true) > $enviada ? $etapa : null;
    }
}
