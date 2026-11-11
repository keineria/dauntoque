<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Campana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CampanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->esAdmin();
    }

    protected function prepareForValidation(): void
    {
        // UTM en minúsculas y sin espacios ni tildes: «Lanzamiento Navidad» → lanzamiento_navidad
        $utm = fn (?string $v) => trim(Str::of((string) $v)->ascii()->lower()->replaceMatches('/[^a-z0-9._-]+/', '_')->value(), '_');
        $this->merge([
            'utm_source' => $utm($this->input('utm_source')),
            'utm_medium' => $utm($this->input('utm_medium')),
            'utm_campaign' => $utm($this->input('utm_campaign') ?: $this->input('nombre')),
            'presupuesto' => $this->filled('presupuesto') ? preg_replace('/[^\d]/', '', (string) $this->input('presupuesto')) : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'canal' => ['required', Rule::in(array_keys(Campana::CANALES))],
            'utm_source' => ['required', 'string', 'max:100'],
            'utm_medium' => ['required', 'string', 'max:100'],
            'utm_campaign' => ['required', 'string', 'max:100', Rule::unique('campanas', 'utm_campaign')->ignore($this->route('campana'))],
            'destino' => ['required', 'url:https', 'max:500', 'regex:#^https://([a-z0-9-]+\.)*dauntoque\.com(/|$|\?)#i'],
            'presupuesto' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'moneda' => ['required', Rule::in(['COP', 'USD'])],
            'inicia_en' => ['nullable', 'date'],
            'termina_en' => ['nullable', 'date', 'after_or_equal:inicia_en'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'utm_campaign.unique' => 'Ya hay una campaña con ese nombre de UTM. Usa otro para no mezclar los resultados.',
            'destino.regex' => 'El enlace debe llevar a una página de dauntoque.com.',
            'termina_en.after_or_equal' => 'La fecha final no puede ser antes de la de inicio.',
        ];
    }
}
