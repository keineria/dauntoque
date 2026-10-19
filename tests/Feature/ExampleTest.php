<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** La raíz del portal lleva al inicio de sesión (routes/web.php). */
    public function test_la_raiz_del_portal_lleva_al_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
