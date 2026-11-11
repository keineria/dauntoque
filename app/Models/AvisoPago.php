<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/** Huella de cada aviso (webhook) de una pasarela de pago, para ver en el administrador que la conexión funciona. */
class AvisoPago extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    protected $table = 'avisos_pago';

    protected $fillable = ['proveedor', 'tipo', 'resultado', 'referencia'];

    protected $casts = ['created_at' => 'datetime'];

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(6));
    }

    public static function anotar(string $proveedor, ?string $tipo, string $resultado, ?string $referencia = null): void
    {
        try {
            static::create([
                'proveedor' => $proveedor,
                'tipo' => $tipo ? mb_substr($tipo, 0, 40) : null,
                'resultado' => $resultado,
                'referencia' => $referencia ? mb_substr($referencia, 0, 80) : null,
            ]);
        } catch (\Throwable) {
            // La huella nunca debe impedir que el aviso de pago se procese
        }
    }
}
