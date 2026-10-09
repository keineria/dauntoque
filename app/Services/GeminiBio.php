<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiBio
{
    /** Devuelve una bio generada por Gemini, o null si no hay clave o la API falla. */
    public function generar(string $profesion, string $ideas): ?string
    {
        $clave = trim((string) config('services.gemini.key'));
        if ($clave === '') {
            return null;
        }

        $prompt = "Actúa como un experto en marca personal y copywriter. Redacta una biografía corta, moderna, persuasiva y elegante (MÁXIMO 35 palabras) para una tarjeta de presentación digital. Profesión: '$profesion'. Contexto o ideas sueltas: '$ideas'. REGLAS: Escríbela en primera persona. No uses comillas. No uses viñetas. Lista para publicarse directamente.";

        try {
            $respuesta = Http::timeout(config('services.gemini.timeout', 15))
                ->withHeaders(['x-goog-api-key' => $clave]) // la clave no viaja en la URL
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/' . config('services.gemini.model') . ':generateContent',
                    ['contents' => [['parts' => [['text' => $prompt]]]]]
                );
        } catch (\Throwable $e) {
            Log::error('Gemini: sin conexión', ['error' => $e->getMessage()]);
            return null;
        }

        if ($respuesta->failed()) {
            Log::error('Gemini: respuesta con error', ['status' => $respuesta->status(), 'body' => mb_substr($respuesta->body(), 0, 500)]);
            return null;
        }

        // Une las partes de texto (los modelos con "thinking" pueden devolver varias)
        $texto = collect($respuesta->json('candidates.0.content.parts', []))
            ->reject(fn ($parte) => ! empty($parte['thought']))
            ->pluck('text')
            ->filter()
            ->implode(' ');

        $texto = str_replace(['*', '"'], '', trim($texto));

        return strlen($texto) > 5 ? $texto : null;
    }
}
