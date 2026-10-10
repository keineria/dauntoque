<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** País de venta (CO, US): moneda, idioma por defecto y WhatsApp de ventas. */
class Market extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['code', 'name', 'currency', 'locale_default', 'whatsapp', 'active'];

    protected $casts = ['active' => 'boolean'];

    /** Países activos para elegir en el panel: [code => name]. */
    public static function opciones(): array
    {
        return static::where('active', true)->orderBy('code')->pluck('name', 'code')->all();
    }
}
