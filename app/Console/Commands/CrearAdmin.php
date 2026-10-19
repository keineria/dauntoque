<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Random\Randomizer;

/**
 * Da el rol de administrador a un correo. Si la cuenta no existe la crea con una contraseña temporal
 * (se muestra una sola vez en pantalla y se cambia al entrar). Con --nueva-clave también reinicia la de una cuenta existente.
 */
class CrearAdmin extends Command
{
    protected $signature = 'admin:crear {correo} {--nombre=Administrador} {--nueva-clave : Reinicia la contraseña de una cuenta que ya existe}';

    protected $description = 'Crea o asciende una cuenta de administrador con contraseña temporal';

    public function handle(): int
    {
        $correo = mb_strtolower(trim($this->argument('correo')));
        if (! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->error('Ese correo no es válido.');

            return self::FAILURE;
        }

        $usuario = User::where('email', $correo)->first();
        $clave = null;
        if (! $usuario || $this->option('nueva-clave')) {
            $clave = self::claveTemporal();
        }

        $usuario ??= new User(['name' => $this->option('nombre'), 'email' => $correo]);
        $usuario->forceFill(array_filter([
            'es_admin' => true,
            'email_verified_at' => $usuario->email_verified_at ?? now(),
            'password' => $clave ? Hash::make($clave) : null,
            'debe_cambiar_clave' => $clave ? true : null,
        ], fn ($v) => $v !== null))->save();

        $this->info(($usuario->wasRecentlyCreated ? 'Cuenta creada' : 'Cuenta actualizada')." como administrador: $correo");
        if ($clave) {
            $this->line('Contraseña temporal (cópiala ahora; no se vuelve a mostrar): '.$clave);
            $this->line('Al entrar, el portal te pedirá cambiarla.');
        } else {
            $this->line('La cuenta ya existía: conserva su contraseña. Usa --nueva-clave para darle una temporal.');
        }

        return self::SUCCESS;
    }

    /** 16 caracteres con mayúsculas, minúsculas, números y símbolos, sin letras que se confundan. */
    public static function claveTemporal(): string
    {
        $grupos = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnpqrstuvwxyz', '23456789', '!@#%*?-'];
        $clave = array_map(fn ($g) => $g[random_int(0, strlen($g) - 1)], $grupos);
        $todos = implode('', $grupos);
        while (count($clave) < 16) {
            $clave[] = $todos[random_int(0, strlen($todos) - 1)];
        }

        return implode('', (new Randomizer)->shuffleArray($clave));
    }
}
