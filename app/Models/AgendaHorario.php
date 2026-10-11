<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Una franja del horario semanal (dia_semana 0 = domingo ... 6 = sábado), en la hora local de la agenda. */
class AgendaHorario extends Model
{
    protected $table = 'agenda_horarios';

    public $timestamps = false;

    protected $fillable = ['user_id', 'dia_semana', 'desde', 'hasta'];
}
