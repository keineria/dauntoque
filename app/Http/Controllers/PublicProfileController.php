<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Support\ImageStore;
use App\Support\RedesSociales;

class PublicProfileController extends Controller
{
    /**
     * Cualquier nickname del usuario lleva a su perfil activo.
     * Devuelve [perfil activo, nickname del enlace maestro].
     */
    private function resolver(string $nickname): array
    {
        $perfilReferencia = Profile::where('nickname', $nickname)->firstOrFail();

        $linkMaestro = Profile::maestroDe($perfilReferencia->user_id)?->nickname ?? $nickname;
        $perfil = Profile::activoDe($perfilReferencia->user_id) ?? abort(404);

        return [$perfil, $linkMaestro];
    }

    public function show($nickname)
    {
        [$perfil, $linkMaestro] = $this->resolver($nickname);

        $galeria = is_string($perfil->galeria) ? json_decode($perfil->galeria, true) : ($perfil->galeria ?? []);
        $diseno = is_array($perfil->design_config) ? $perfil->design_config : (json_decode($perfil->design_config ?? '', true) ?: []);
        $color = $diseno['color_primario'] ?? '#3b82f6';

        // Datos que pinta la plantilla compartida (partials/perfil-publico), la misma de la vista previa del editor
        $datos = [
            'nombre' => $perfil->nombre ?: $perfil->nickname,
            'profesion' => $perfil->profesion,
            'bio' => $perfil->bio,
            'foto' => ImageStore::esPropia($perfil->foto_perfil) ? $perfil->foto_perfil : null,
            'tipo' => $perfil->tipo_perfil,
            'alineacion' => $diseno['alineacion'] ?? $perfil->alineacion ?? 'centro',
            'estructura' => $diseno['layout_estructura'] ?? 'clasica',
            'tema' => in_array($diseno['tema_base'] ?? '', ['crystal', 'matte', 'luxury', 'corporativo', 'sport', 'minimalista', 'onyx', 'medianoche', 'rose', 'esmeralda'], true) ? $diseno['tema_base'] : 'crystal',
            'color' => preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) ? $color : '#3b82f6',
            'forma' => in_array($diseno['forma_botones'] ?? '', ['rounded-none', 'rounded-md', 'rounded-xl', 'rounded-2xl', 'rounded-full', '-skew-x-12'], true) ? $diseno['forma_botones'] : 'rounded-2xl',
            'telefono' => $perfil->telefono,
            'correo' => $perfil->correo_contacto,
            'direccion' => $perfil->direccion,
            'zelle' => $perfil->zelle,
            'cashapp' => $perfil->cashapp,
            'venmo' => $perfil->venmo,
            // El SVG de cada red sale del catálogo del servidor, nunca de lo guardado (anti-XSS)
            'redes' => RedesSociales::sanear($perfil->redes_sociales),
            'galeria' => array_values(array_filter((array) $galeria, fn ($img) => ImageStore::esPropia($img))),
            'enlace' => url('/' . $linkMaestro),
            'vcf' => url('/' . $linkMaestro . '/vcf'),
            'preview' => false,
        ];

        return view('public.profile', compact('perfil', 'datos'));
    }

    public function descargarVCF($nickname)
    {
        [$perfil, $linkMaestro] = $this->resolver($nickname);

        // Un salto de línea dentro de un campo rompería el formato vCard
        $linea = fn ($valor) => str_replace(["\r", "\n"], ' ', (string) $valor);

        $vcf = "BEGIN:VCARD\r\n";
        $vcf .= "VERSION:3.0\r\n";
        $vcf .= "FN:" . $linea($perfil->nombre) . "\r\n";
        if($perfil->profesion) $vcf .= "TITLE:" . $linea($perfil->profesion) . "\r\n";
        if($perfil->telefono) $vcf .= "TEL;TYPE=CELL:" . $linea($perfil->telefono) . "\r\n";
        if($perfil->correo_contacto) $vcf .= "EMAIL;TYPE=WORK:" . $linea($perfil->correo_contacto) . "\r\n";
        if($perfil->direccion) $vcf .= "ADR;TYPE=WORK:;;;" . $linea($perfil->direccion) . ";;;\r\n";

        // URL Limpia en la agenda del celular
        $vcf .= "URL:" . url('/' . $linkMaestro) . "\r\n";

        // Solo se incrusta la foto si es una imagen propia del portal (nunca otros archivos del servidor)
        if($perfil->foto_perfil && ImageStore::esPropia($perfil->foto_perfil)) {
            $imagePath = realpath(public_path(ltrim($perfil->foto_perfil, '/')));
            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
            $data = file_get_contents($imagePath);
            $base64 = base64_encode($data);
            $vcf .= "PHOTO;ENCODING=b;TYPE=" . strtoupper($type) . ":" . $base64 . "\r\n";
        }
        $vcf .= "END:VCARD\r\n";

        $archivo = str_replace(['"', '\\', '/', "\r", "\n"], '', (string) $perfil->nombre) ?: 'contacto';

        return response($vcf)
            ->header('Content-Type', 'text/vcard')
            ->header('Content-Disposition', 'attachment; filename="' . $archivo . '.vcf"');
    }
}
