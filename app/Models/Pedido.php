<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Compra de una tarjeta desde el sitio de ventas. */
class Pedido extends Model
{
    public const ACABADOS = ['matte-black' => 'Matte Black', 'crystal' => 'Crystal'];

    protected $fillable = [
        'user_id', 'nombre', 'correo', 'celular', 'documento', 'departamento', 'ciudad', 'direccion',
        'acabado', 'nombre_tarjeta', 'pais', 'moneda', 'monto', 'estado', 'link_id', 'pago_proveedor_id',
        'pagado_en', 'correo_enviado_en', 'promo_de_pedido_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'pagado_en' => 'datetime',
        'correo_enviado_en' => 'datetime',
        'promo_enviada_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn (Pedido $p) => $p->token ??= Str::random(40));
        static::created(fn (Pedido $p) => $p->forceFill(['codigo' => 'DT-' . str_pad((string) $p->id, 5, '0', STR_PAD_LEFT)])->saveQuietly());
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    /** Código de activación de la tarjeta, creado al confirmarse el pago. */
    public function chip()
    {
        return $this->hasOne(Chip::class);
    }

    public function acabadoNombre(): string
    {
        return self::ACABADOS[$this->acabado] ?? $this->acabado;
    }

    /** "$299.900 COP" */
    public function totalTexto(): string
    {
        return '$' . number_format((float) $this->monto, $this->moneda === 'COP' ? 0 : 2, ',', '.') . ' ' . $this->moneda;
    }

    /** Campos para las plantillas de correo editables ({{nombre}}, {{pedido}}, …). */
    public function valoresCorreo(): array
    {
        return [
            'nombre' => Str::before($this->nombre, ' '),
            'nombre_completo' => $this->nombre,
            'correo' => $this->correo,
            'celular' => $this->celular,
            'documento' => $this->documento,
            'pedido' => $this->codigo,
            'fecha' => ($this->pagado_en ?? $this->created_at ?? now())->format('d/m/Y'),
            'tarjeta' => $this->acabadoNombre(),
            'nombre_tarjeta' => $this->nombre_tarjeta,
            'total' => $this->totalTexto(),
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'departamento' => $this->departamento,
            'enlace_registro' => $this->enlaceRegistro(),
            'codigo_activacion' => $this->chip?->codigo,
            'enlace_grabar' => $this->chip?->url(),
            'enlace_panel' => url('/dashboard'),
        ];
    }

    /** El correo de la compra ya tiene cuenta en el portal (compró una segunda tarjeta o se registró antes de pagar). */
    public function tieneCuenta(): bool
    {
        return User::where('email', $this->correo)->exists();
    }

    /**
     * Enlace para crear la cuenta en el portal con el correo ya escrito.
     * Si ese correo ya tiene cuenta, lleva a iniciar sesión (registrarse otra vez daría "correo en uso").
     */
    public function enlaceRegistro(): string
    {
        if ($this->tieneCuenta()) {
            return url('/login');
        }

        return url('/register?' . http_build_query(['correo' => $this->correo, 'pedido' => $this->codigo]));
    }
}
