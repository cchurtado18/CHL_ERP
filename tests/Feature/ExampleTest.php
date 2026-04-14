<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz exige autenticación (dashboard admin); invitados reciben redirección al login.
     */
    public function test_root_redirects_guests(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
