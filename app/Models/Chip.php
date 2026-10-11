<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Chip NFC vendido. Solo los chips registrados se pueden activar en /activar/{codigo}. */
class Chip extends Model
{
    protected $fillable = ['codigo', 'nota'];

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

    public static function nuevoCodigo(): string
    {
        do {
            $codigo = Str::lower(Str::random(config('chips.largo_codigo')));
        } while (static::where('codigo', $codigo)->exists());

        return $codigo;
    }

    public function url(): string
    {
        return url('/activar/' . $this->codigo);
    }
}
