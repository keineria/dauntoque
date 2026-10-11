<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Reglas de la agenda de un usuario: zona horaria, intervalos, antelación y Google Calendar. */
class AgendaAjuste extends Model
{
    protected $table = 'agenda_ajustes';

    protected $fillable = [
        'user_id', 'zona_horaria', 'intervalo_min', 'antelacion_min', 'dias_adelante', 'limite_cancelar_horas',
        'mensaje_whatsapp', 'google_token', 'google_correo',
    ];

    protected $casts = ['google_token' => 'encrypted'];

    protected $hidden = ['google_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Ajustes del usuario; si no existen se crean con la zona horaria de su país. */
    public static function de(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId], [
            'zona_horaria' => config('agenda.zona_por_pais.' . \App\Support\MediosPago::pais(Profile::activoDe($userId)?->pais), 'America/Bogota'),
        ]);
    }

    public function conGoogle(): bool
    {
        return filled($this->google_token);
    }
}
