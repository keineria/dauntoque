<?php

namespace App\Http\Controllers;

use App\Models\Profile;
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
        $redes = is_string($perfil->redes_sociales) ? json_decode($perfil->redes_sociales, true) : ($perfil->redes_sociales ?? []);

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
        
        $vcf = "BEGIN:VCARD\r\n";
        $vcf .= "VERSION:3.0\r\n";
        $vcf .= "FN:" . $perfil->nombre . "\r\n";
        if($perfil->profesion) $vcf .= "TITLE:" . $perfil->profesion . "\r\n";
        if($perfil->telefono) $vcf .= "TEL;TYPE=CELL:" . $perfil->telefono . "\r\n";
        if($perfil->correo_contacto) $vcf .= "EMAIL;TYPE=WORK:" . $perfil->correo_contacto . "\r\n";
        if($perfil->direccion) $vcf .= "ADR;TYPE=WORK:;;;" . $perfil->direccion . ";;;\r\n";
        
        // URL Limpia en la agenda del celular
        $vcf .= "URL:" . url('/' . $linkMaestro) . "\r\n";
        
        if($perfil->foto_perfil) {
            $imagePath = public_path(ltrim($perfil->foto_perfil, '/'));
            if(file_exists($imagePath)) {
                $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                $data = file_get_contents($imagePath);
                $base64 = base64_encode($data);
                $vcf .= "PHOTO;ENCODING=b;TYPE=" . strtoupper($type) . ":" . $base64 . "\r\n";
            }
        }
        $vcf .= "END:VCARD\r\n";

        return response($vcf)
            ->header('Content-Type', 'text/vcard')
            ->header('Content-Disposition', 'attachment; filename="' . $perfil->nombre . '.vcf"');
    }
}