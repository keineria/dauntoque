<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Support\ImageStore;
use App\Support\RedesSociales;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function show($nickname)
    {
        $perfilReferencia = Profile::where('nickname', $nickname)->firstOrFail();

        // Identificamos el enlace original pase lo que pase
        $perfilMaestro = Profile::where('user_id', $perfilReferencia->user_id)->orderBy('id', 'asc')->first();
        $linkMaestro = $perfilMaestro ? $perfilMaestro->nickname : $nickname;

        $perfil = Profile::where('user_id', $perfilReferencia->user_id)
            ->orderByRaw('CASE WHEN nfc_uid IS NOT NULL AND nfc_uid != "" THEN 1 ELSE 0 END DESC')
            ->orderBy('updated_at', 'desc')
            ->firstOrFail();

        $galeria = is_string($perfil->galeria) ? json_decode($perfil->galeria, true) : ($perfil->galeria ?? []);
        // El SVG de cada red sale del catálogo del servidor, nunca de lo guardado (anti-XSS)
        $redes = RedesSociales::sanear($perfil->redes_sociales);

        return view('public.profile', compact('perfil', 'galeria', 'redes', 'linkMaestro'));
    }

    public function descargarVCF($nickname)
    {
        $perfilReferencia = Profile::where('nickname', $nickname)->firstOrFail();

        $perfilMaestro = Profile::where('user_id', $perfilReferencia->user_id)->orderBy('id', 'asc')->first();
        $linkMaestro = $perfilMaestro ? $perfilMaestro->nickname : $nickname;

        $perfil = Profile::where('user_id', $perfilReferencia->user_id)
            ->orderByRaw('CASE WHEN nfc_uid IS NOT NULL AND nfc_uid != "" THEN 1 ELSE 0 END DESC')
            ->orderBy('updated_at', 'desc')
            ->firstOrFail();

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
