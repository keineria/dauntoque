<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Random\Randomizer;

/** Chip NFC vendido. Solo los chips registrados se pueden activar en /activar/{codigo}. */
class Chip extends Model
{
    /** Letras y números que no se confunden al escribirlos a mano (sin i, l, o, 0 ni 1). */
    private const LEGIBLES = 'abcdefghjkmnpqrstuvwxyz23456789';

    protected $fillable = ['codigo', 'nota', 'pedido_id'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /** Código de activación de una compra; se crea una sola vez aunque Bold repita el aviso. */
    public static function deCompra(Pedido $pedido): self
    {
        return static::firstOrCreate(['pedido_id' => $pedido->id], [
            'codigo' => static::nuevoCodigo(),
            'nota' => "Pedido {$pedido->codigo} · {$pedido->nombre}",
        ]);
    }

    /**
     * Chip a partir de lo que el cliente escribe en su panel: el código (con o sin guion,
     * en mayúsculas o con espacios) o el enlace completo grabado en la tarjeta.
     */
    public static function escrito(string $texto): ?self
    {
        $codigo = static::extraerCodigo(str_replace(' ', '', $texto));
        if ($codigo === null) {
            return null;
        }
        $minus = Str::lower($codigo);
        $legible = preg_match('~^[a-z0-9]{8}$~', $minus) ? substr($minus, 0, 4) . '-' . substr($minus, 4) : $minus;

        return static::whereIn('codigo', array_unique([$codigo, $minus, $legible]))->first();
    }

    public static function habilitado(string $codigo): bool
    {
        return ! config('chips.validar') || static::where('codigo', $codigo)->exists();
    }

    /** Acepta el código suelto o la URL completa grabada en el chip (…/activar/abc123). */
    public static function extraerCodigo(string $linea): ?string
    {
        $linea = trim($linea);
        if (preg_match('~/activar/([^/?#\s]+)~', $linea, $m)) {
            $linea = rawurldecode($m[1]);
        }

        return preg_match('~^[A-Za-z0-9._-]{4,100}$~', $linea) ? $linea : null;
    }

    /** Código nuevo tipo "k7mq-2xpa": imposible de adivinar y fácil de escribir a mano. */
    public static function nuevoCodigo(): string
    {
        $azar = new Randomizer;
        do {
            $codigo = implode('-', str_split($azar->getBytesFromString(self::LEGIBLES, config('chips.largo_codigo')), 4));
        } while (static::where('codigo', $codigo)->exists());

        return $codigo;
    }

    public function url(): string
    {
        return url('/activar/' . $this->codigo);
    }
}
