<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\TemaPanel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Paleta y apariencia del panel elegidas en Mi cuenta. */
class GuardarTemaPanelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'paleta' => ['required', 'string', Rule::in(array_keys(TemaPanel::PALETAS))],
            'apariencia' => ['required', 'string', Rule::in(array_keys(TemaPanel::APARIENCIAS))],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['in' => 'Elige una de las opciones de la lista.'];
    }
}
