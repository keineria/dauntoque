<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Calificación de 1 a 5 estrellas que un cliente le deja al dueño de una tarjeta (app Califícame). */
class Calificacion extends Model
{
    protected $table = 'calificaciones';

    protected $fillable = ['user_id', 'cita_id', 'estrellas', 'comentario', 'nombre', 'contacto', 'visible'];

    protected $casts = ['estrellas' => 'integer', 'visible' => 'boolean'];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    /** Buena (4 o 5 estrellas): el dueño puede mostrarla en su perfil. Las demás son privadas. */
    public function buena(): bool
    {
        return $this->estrellas >= config('calificame.estrellas_buenas');
    }

    /** Se puede mostrar en el perfil: buena y con comentario. */
    public function publicable(): bool
    {
        return $this->buena() && filled($this->comentario);
    }
}
