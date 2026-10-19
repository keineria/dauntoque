<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pedido de ayuda de un cliente: ticket (se responde por escrito, sin prisa) o chat en vivo.
 * Un chat nuevo entra a la cola (en_cola) y pasa a abierta cuando un agente lo toma, por orden de llegada.
 */
class SoporteConversacion extends Model
{
    protected $table = 'soporte_conversaciones';

    protected $fillable = ['user_id', 'tipo', 'asunto', 'estado', 'agente_id', 'tomada_en', 'cerrada_en', 'ultimo_mensaje_en'];

    protected $casts = [
        'tomada_en' => 'datetime',
        'cerrada_en' => 'datetime',
        'ultimo_mensaje_en' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agente_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(SoporteMensaje::class, 'conversacion_id');
    }

    /** Chats esperando agente, del más antiguo al más nuevo. */
    public function scopeEnCola(Builder $query): Builder
    {
        return $query->where('tipo', 'chat')->where('estado', 'en_cola')->orderBy('created_at')->orderBy('id');
    }

    /** Puesto en la cola (1 = el siguiente en ser atendido), o null si no está esperando. */
    public function puestoEnCola(): ?int
    {
        if ($this->estado !== 'en_cola') {
            return null;
        }

        return static::where('tipo', 'chat')->where('estado', 'en_cola')
            ->where(fn ($q) => $q->where('created_at', '<', $this->created_at)
                ->orWhere(fn ($q) => $q->where('created_at', $this->created_at)->where('id', '<', $this->id)))
            ->count() + 1;
    }

    public function escribir(User $autor, string $texto, bool $comoAgente): SoporteMensaje
    {
        $mensaje = $this->mensajes()->create(['user_id' => $autor->id, 'es_agente' => $comoAgente, 'texto' => $texto]);
        $this->update(['ultimo_mensaje_en' => now()]);

        return $mensaje;
    }
}
