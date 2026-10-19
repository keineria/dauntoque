<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/** Ajuste editable desde el administrador (clave → valor). Se guardan en caché hasta que cambian. */
class Ajuste extends Model
{
    protected $primaryKey = 'clave';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['clave', 'valor'];

    private const CACHE = 'ajustes.todos';

    /** @return array<string, string|null> */
    public static function todos(): array
    {
        // Antes de migrar (o sin base de datos, por ejemplo en composer install) no hay ajustes
        try {
            if (is_array($guardados = Cache::get(self::CACHE))) {
                return $guardados;
            }
            if (! Schema::hasTable('ajustes')) {
                return [];
            }
            $todos = static::query()->pluck('valor', 'clave')->all();
            Cache::forever(self::CACHE, $todos);
        } catch (\Throwable) {
            return [];
        }

        return $todos;
    }

    public static function valor(string $clave, mixed $porDefecto = null): mixed
    {
        $valor = static::todos()[$clave] ?? null;

        return $valor === null || $valor === '' ? $porDefecto : $valor;
    }

    /** @param  array<string, mixed>  $valores */
    public static function guardar(array $valores): void
    {
        foreach ($valores as $clave => $valor) {
            static::updateOrCreate(['clave' => $clave], ['valor' => is_bool($valor) ? ($valor ? '1' : '0') : $valor]);
        }
        Cache::forget(self::CACHE);
    }
}
