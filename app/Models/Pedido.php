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

    public function acabadoNombre(): string
    {
        return self::ACABADOS[$this->acabado] ?? $this->acabado;
    }

    /** "$299.900 COP" */
    public function totalTexto(): string
    {
        return '$' . number_format((float) $this->monto, $this->moneda === 'COP' ? 0 : 2, ',', '.') . ' ' . $this->moneda;
    }

    /** Enlace para crear la cuenta en el portal con el correo ya escrito. */
    public function enlaceRegistro(): string
    {
        return url('/register?' . http_build_query(['correo' => $this->correo, 'pedido' => $this->codigo]));
    }
}
