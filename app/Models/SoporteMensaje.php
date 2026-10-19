<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoporteMensaje extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'soporte_mensajes';

    protected $fillable = ['conversacion_id', 'user_id', 'es_agente', 'texto'];

    protected $casts = ['es_agente' => 'boolean'];

    /** @return array{id: int, texto: string, agente: bool, hora: string} */
    public function paraVista(): array
    {
        return ['id' => $this->id, 'texto' => $this->texto, 'agente' => $this->es_agente, 'hora' => $this->created_at->format('d/m H:i')];
    }
}
