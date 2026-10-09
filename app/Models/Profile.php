<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'nickname', 'tipo_perfil', 'nombre', 'profesion', 'bio',
        'plantilla', 'alineacion', 'telefono', 'correo_contacto', 'direccion',
        'zelle', 'cashapp', 'venmo', 'redes_sociales', 'galeria', 'nfc_uid', 'activo',
        'foto_perfil', 'foto_portada', 'titulo_galeria' // <-- Archivos nuevos autorizados
    ];

    protected $casts = [
        'redes_sociales' => 'array',
        'galeria' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}