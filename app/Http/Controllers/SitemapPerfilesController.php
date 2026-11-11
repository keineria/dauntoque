<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Seo\SeoPerfil;
use Illuminate\Http\Response;

/** Mapa del sitio con los perfiles públicos (lo anuncia robots.txt junto al del sitio de ventas). */
class SitemapPerfilesController extends Controller
{
    public function __invoke(): Response
    {
        return response()
            ->view('sitio.sitemap-perfiles', ['perfiles' => SeoPerfil::mapa()])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
