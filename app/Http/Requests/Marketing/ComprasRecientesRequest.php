<?php

declare(strict_types=1);

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;

/** País de los avisos de compras del sitio de ventas (?pais=CO). */
class ComprasRecientesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['pais' => ['nullable', 'string', 'in:CO,US']];
    }

    public function pais(): string
    {
        return (string) ($this->validated()['pais'] ?? 'CO');
    }
}
