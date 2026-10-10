<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pago en línea de un usuario (renovación del servicio de su tarjeta). */
class Pago extends Model
{
    protected $fillable = [
        'user_id', 'tarjeta_id', 'concepto', 'proveedor', 'pais', 'moneda', 'monto',
        'estado', 'link_id', 'url', 'pago_proveedor_id', 'pagado_en',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'pagado_en' => 'datetime',
    ];

    public function tarjeta()
    {
        return $this->belongsTo(Tarjeta::class);
    }
}
