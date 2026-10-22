<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'dos_pasos_secreto', 'dos_pasos_respaldo'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'es_admin' => 'boolean',
            'debe_cambiar_clave' => 'boolean',
            'dos_pasos_secreto' => 'encrypted',
            'dos_pasos_respaldo' => 'encrypted:array',
            'dos_pasos_activo_en' => 'datetime',
            'recordatorio_uso_en' => 'datetime',
            'precio_renovacion' => 'integer',
        ];
    }

    public function perfiles()
    {
        return $this->hasMany(Profile::class);
    }

    public function tarjetas()
    {
        return $this->hasMany(Tarjeta::class);
    }

    /** Dueño del negocio: entra al panel de administración (rol es_admin o config/chips.php, ADMIN_CORREOS). */
    public function esAdmin(): bool
    {
        return (bool) $this->es_admin || in_array(strtolower((string) $this->email), config('chips.admins'), true);
    }

    /** ¿Pide el código de la app autenticadora al iniciar sesión? */
    public function tieneDosPasos(): bool
    {
        return $this->dos_pasos_activo_en !== null && filled($this->dos_pasos_secreto);
    }
}
