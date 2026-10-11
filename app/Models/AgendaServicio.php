<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Lo que el cliente puede reservar: nombre, duración, precio y si requiere aprobación del dueño. */
class AgendaServicio extends Model
{
    protected $table = 'agenda_servicios';

    protected $fillable = ['user_id', 'nombre', 'descripcion', 'duracion_min', 'colchon_min', 'precio', 'requiere_aprobacion', 'activo', 'orden'];

    protected $casts = ['requiere_aprobacion' => 'boolean', 'activo' => 'boolean', 'precio' => 'decimal:2'];
}
