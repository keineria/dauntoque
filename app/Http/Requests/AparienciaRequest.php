<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Mi cuenta → Apariencia: una paleta y un modo de config/apariencia.php. */
class AparienciaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'paleta' => ['required', Rule::in(array_keys(config('apariencia.paletas')))],
            'modo' => ['required', Rule::in(array_keys(config('apariencia.modos')))],
        ];
    }

    public function messages(): array
    {
        return ['paleta.*' => 'Elige una de las paletas de la lista.', 'modo.*' => 'Elige automático, claro u oscuro.'];
    }
}
