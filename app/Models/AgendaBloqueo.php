<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tiempo cerrado de la agenda (vacaciones, festivos, una tarde libre), en UTC. */
class AgendaBloqueo extends Model
{
    protected $table = 'agenda_bloqueos';

    protected $fillable = ['user_id', 'desde', 'hasta', 'motivo'];

    protected $casts = ['desde' => 'datetime', 'hasta' => 'datetime'];
}
