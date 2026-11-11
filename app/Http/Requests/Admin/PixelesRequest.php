<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Services\Marketing\Etiquetas;
use Illuminate\Foundation\Http\FormRequest;

class PixelesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->esAdmin();
    }

    protected function prepareForValidation(): void
    {
        // Se aceptan IDs pegados con espacios o en minúsculas (g-ab12 → G-AB12)
        $limpio = [];
        foreach (Etiquetas::PIXELES as $clave => $pixel) {
            $valor = preg_replace('/\s+/', '', (string) $this->input($clave));
            $limpio[$clave] = in_array($clave, ['ga4', 'gads', 'tiktok', 'gtm'], true) ? strtoupper($valor) : ($clave === 'clarity' ? strtolower($valor) : $valor);
        }
        $this->merge($limpio);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $reglas = [];
        foreach (Etiquetas::PIXELES as $clave => $pixel) {
            $reglas[$clave] = ['nullable', 'string', 'regex:' . $pixel['formato']];
        }
        $reglas['gads_compra'][] = 'required_with:gads';

        return $reglas;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $mensajes = ['gads_compra.required_with' => 'Pega también la etiqueta de la conversión «Compra» de Google Ads.'];
        foreach (Etiquetas::PIXELES as $clave => $pixel) {
            $mensajes[$clave . '.regex'] = 'El ID de ' . $pixel['nombre'] . ' no tiene el formato esperado (ejemplo: ' . $pixel['ejemplo'] . ').';
        }

        return $mensajes;
    }
}
