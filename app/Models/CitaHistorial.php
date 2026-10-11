<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Quién hizo qué con una cita y cuándo (creada, aprobada, movida, cancelada...). */
class CitaHistorial extends Model
{
    protected $table = 'cita_historial';

    public $timestamps = false;

    protected $fillable = ['cita_id', 'accion', 'por', 'detalle', 'created_at'];

    protected $casts = ['detalle' => 'array', 'created_at' => 'datetime'];
}
