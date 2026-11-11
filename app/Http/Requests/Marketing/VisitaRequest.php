<?php

declare(strict_types=1);

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;

/** Visita que manda /etiquetas.js con sendBeacon (cuerpo JSON como texto plano, sin token CSRF). */
class VisitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $datos = json_decode((string) $this->getContent(), true);
        if (is_array($datos)) {
            $this->merge($datos);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'v' => ['required', 'string', 'regex:/^[a-f0-9]{32}$/'],
            'p' => ['required', 'string', 'max:190'],
            'r' => ['nullable', 'string', 'max:120'],
            'u' => ['nullable', 'array'],
            'u.utm_source' => ['nullable', 'string', 'max:100'],
            'u.utm_medium' => ['nullable', 'string', 'max:100'],
            'u.utm_campaign' => ['nullable', 'string', 'max:100'],
            'u.utm_content' => ['nullable', 'string', 'max:100'],
            'u.utm_term' => ['nullable', 'string', 'max:100'],
            'u.clic' => ['nullable', 'in:gclid,fbclid,ttclid'],
        ];
    }

    /** Una visita inválida no muestra errores: simplemente no se guarda. */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->noContent());
    }
}
