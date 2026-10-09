<?php

namespace App\Console\Commands;

use App\Models\Profile;
use Illuminate\Console\Command;

/**
 * Mueve fuera de la web las imágenes que ya no usa ningún perfil.
 * No borra nada: las deja en /home/portal.dauntoque.com/backups/uploads-huerfanas-FECHA.
 */
class LimpiarUploads extends Command
{
    protected $signature = 'uploads:limpiar {--mover : Mover los archivos (sin esta opción solo los lista)}';

    protected $description = 'Lista o aparta las imágenes de public/uploads que ningún perfil usa';

    public function handle(): int
    {
        $enUso = Profile::all()->flatMap(fn ($p) => array_merge([$p->foto_perfil], (array) $p->galeria))
            ->filter()->map(fn ($ruta) => basename($ruta))->flip();

        $huerfanas = collect(glob(public_path('uploads/*')))
            ->filter(fn ($f) => is_file($f) && ! $enUso->has(basename($f)));

        $mb = round($huerfanas->sum(fn ($f) => filesize($f)) / 1048576, 1);
        $this->info("{$huerfanas->count()} imágenes sin usar ({$mb} MB).");

        if (! $this->option('mover') || $huerfanas->isEmpty()) {
            $huerfanas->each(fn ($f) => $this->line('  ' . basename($f)));
            return self::SUCCESS;
        }

        $destino = dirname(base_path()) . '/backups/uploads-huerfanas-' . now()->format('Ymd-His');
        mkdir($destino, 0700, true);
        $huerfanas->each(fn ($f) => rename($f, $destino . '/' . basename($f)));
        $this->info("Movidas a {$destino}");

        return self::SUCCESS;
    }
}
