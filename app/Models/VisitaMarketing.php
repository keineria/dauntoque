<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/** Página vista del sitio de ventas o de la compra, con el origen (UTM) de la visita. Sin datos personales. */
class VisitaMarketing extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    protected $table = 'visitas_marketing';

    protected $fillable = [
        'visitante', 'pagina', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
        'clic', 'referente', 'pais', 'dispositivo',
    ];

    /** Se guardan 13 meses (para comparar con el mismo mes del año anterior). */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(13));
    }
}
