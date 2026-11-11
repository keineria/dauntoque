<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\Ajuste;

/**
 * Píxeles y etiquetas de medición (Meta, Google Analytics 4, Google Ads, TikTok, Tag Manager y Clarity).
 * Solo se guarda el ID público de cada uno (tabla ajustes); /etiquetas.js los carga en el sitio de ventas,
 * el formulario de compra y la página del pedido, y manda los eventos de visita, inicio de compra y compra.
 */
class Etiquetas
{
    /** clave => nombre, formato válido, ejemplo y pasos para encontrar el ID. */
    public const PIXELES = [
        'meta' => [
            'nombre' => 'Píxel de Meta (Facebook e Instagram)',
            'formato' => '/^\d{10,20}$/',
            'ejemplo' => '123456789012345',
            'pasos' => [
                'Entra a business.facebook.com y abre el «Administrador de eventos».',
                'Toca «Conectar orígenes de datos», elige «Web» y luego «Píxel de Meta» (si ya tienes uno, ábrelo).',
                'Copia el «Identificador del conjunto de datos» (solo números) y pégalo aquí.',
            ],
            'comprobar' => 'En el Administrador de eventos, pestaña «Probar eventos», escribe dauntoque.com y navega el sitio.',
        ],
        'ga4' => [
            'nombre' => 'Google Analytics 4',
            'formato' => '/^G-[A-Z0-9]{4,15}$/',
            'ejemplo' => 'G-AB12CD34EF',
            'pasos' => [
                'Entra a analytics.google.com y toca el engranaje «Administrar» (abajo a la izquierda).',
                'Abre «Flujos de datos», elige el flujo «Web» de dauntoque.com (o créalo con esa dirección).',
                'Copia el «ID de medición», que empieza por G-, y pégalo aquí.',
            ],
            'comprobar' => 'En Analytics abre «Informes» → «Tiempo real» y visita el sitio desde tu celular.',
        ],
        'gads' => [
            'nombre' => 'Google Ads (ID de la cuenta)',
            'formato' => '/^AW-\d{6,15}$/',
            'ejemplo' => 'AW-123456789',
            'pasos' => [
                'Entra a ads.google.com → «Objetivos» → «Conversiones» → «Nueva acción de conversión» → «Sitio web».',
                'Escribe dauntoque.com, elige «Agregar una acción de conversión manualmente», categoría «Compra», valor «Usar valores diferentes».',
                'Al final elige «Usar Google tag»: verás una línea send_to: \'AW-123456789/AbC-dEfGh\'. Lo de antes de la barra va aquí.',
            ],
            'comprobar' => 'En Conversiones, la acción pasa de «Sin verificar» a «Registrando conversiones» después de la primera compra.',
        ],
        'gads_compra' => [
            'nombre' => 'Google Ads (etiqueta de la conversión «Compra»)',
            'formato' => '/^[A-Za-z0-9_-]{4,40}$/',
            'ejemplo' => 'AbC-dEfGh',
            'pasos' => [
                'Es lo que va después de la barra en send_to: \'AW-123456789/AbC-dEfGh\' (en el ejemplo, AbC-dEfGh).',
            ],
            'comprobar' => null,
        ],
        'tiktok' => [
            'nombre' => 'Píxel de TikTok',
            'formato' => '/^[A-Z0-9]{15,30}$/',
            'ejemplo' => 'C1A2B3C4D5E6F7G8H9I0',
            'pasos' => [
                'Entra a ads.tiktok.com → «Herramientas» → «Eventos» → «Eventos web» → «Configurar evento web».',
                'Elige «Píxel de TikTok» y «Configurar manualmente», con el nombre DaUnToque.',
                'Copia el ID del píxel (letras mayúsculas y números) y pégalo aquí.',
            ],
            'comprobar' => 'En Eventos web abre el píxel → «Probar eventos» y visita el sitio.',
        ],
        'gtm' => [
            'nombre' => 'Google Tag Manager (opcional)',
            'formato' => '/^GTM-[A-Z0-9]{4,12}$/',
            'ejemplo' => 'GTM-AB12CD3',
            'pasos' => [
                'Solo si una agencia maneja tus etiquetas desde Tag Manager. Entra a tagmanager.google.com y abre el contenedor.',
                'Copia el ID de arriba, que empieza por GTM-. Si lo usas, no repitas aquí los IDs que ya estén dentro del contenedor.',
            ],
            'comprobar' => 'En Tag Manager toca «Vista previa» y escribe dauntoque.com.',
        ],
        'clarity' => [
            'nombre' => 'Microsoft Clarity (mapas de calor y grabaciones, gratis)',
            'formato' => '/^[a-z0-9]{6,20}$/',
            'ejemplo' => 'abc123defg',
            'pasos' => [
                'Entra a clarity.microsoft.com con tu cuenta de Microsoft o Google y crea un proyecto con dauntoque.com.',
                'Ve a «Configuración» → «Información general» y copia el «ID del proyecto».',
            ],
            'comprobar' => 'En Clarity, las primeras grabaciones aparecen unas 2 horas después de las visitas.',
        ],
    ];

    private const PREFIJO = 'px.';

    /** @return array<string, string> solo los conectados */
    public static function ids(): array
    {
        $ids = [];
        foreach (array_keys(self::PIXELES) as $clave) {
            if ($valor = Ajuste::valor(self::PREFIJO . $clave)) {
                $ids[$clave] = (string) $valor;
            }
        }

        return $ids;
    }

    /** @param  array<string, ?string>  $valores */
    public static function guardar(array $valores): void
    {
        $guardar = [];
        foreach (array_keys(self::PIXELES) as $clave) {
            $guardar[self::PREFIJO . $clave] = trim((string) ($valores[$clave] ?? '')) ?: null;
        }
        Ajuste::guardar($guardar);
    }

    /** Código de /etiquetas.js: los IDs conectados + el cargador de cada píxel, el origen de la visita y los eventos. */
    public static function script(): string
    {
        $ids = self::ids();
        $config = [
            'meta' => $ids['meta'] ?? null,
            'ga4' => $ids['ga4'] ?? null,
            'gads' => $ids['gads'] ?? null,
            'gadsCompra' => isset($ids['gads'], $ids['gads_compra']) ? $ids['gads'] . '/' . $ids['gads_compra'] : null,
            'tiktok' => $ids['tiktok'] ?? null,
            'gtm' => $ids['gtm'] ?? null,
            'clarity' => $ids['clarity'] ?? null,
            'visita' => '/etiquetas/visita',
        ];

        return strtr((string) file_get_contents(resource_path('marketing/etiquetas.js')), [
            '__CONFIG__' => json_encode($config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG),
        ]);
    }
}
