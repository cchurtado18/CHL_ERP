<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Facturacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $attrs = []): User
    {
        return User::factory()->create(['rol' => 'admin', 'permisos' => null] + $attrs);
    }

    private function staff(array $permisos, string $rol = 'agente'): User
    {
        return User::factory()->create(['rol' => $rol, 'permisos' => $permisos]);
    }

    private function cliente(string $nombre = 'Cliente'): Cliente
    {
        return Cliente::create([
            'nombre_completo' => $nombre,
            'correo' => strtolower($nombre).'@example.com',
            'telefono' => '0000',
            'direccion' => 'Dirección',
            'tipo_cliente' => 'normal',
            'fecha_registro' => now()->toDateString(),
        ]);
    }

    // --- Rutas que antes quedaban tapadas por /{id} ---

    public function test_static_routes_are_not_shadowed_by_wildcards(): void
    {
        $esperado = [
            '/notificaciones/no-leidas' => 'notificaciones.no-leidas',
            '/tracking/buscar' => 'tracking.buscar',
            '/tracking/proximos-vencer' => 'tracking.proximos-vencer',
            '/tracking/verificar-recordatorios' => 'tracking.verificar-recordatorios',
            '/tracking/12' => 'tracking.show',
            '/notificaciones/12' => 'notificaciones.show',
        ];

        foreach ($esperado as $uri => $nombre) {
            $this->assertSame($nombre, Route::getRoutes()->match(Request::create($uri))->getName(), $uri);
        }
    }

    public function test_unread_notifications_endpoint_responds(): void
    {
        $this->actingAs($this->admin())
            ->getJson('/notificaciones/no-leidas')
            ->assertOk();
    }

    // --- Escalada de privilegios en usuarios ---

    public function test_non_admin_cannot_create_an_admin(): void
    {
        $gestor = $this->staff(['usuarios']);

        $this->actingAs($gestor)->post('/usuarios', [
            'nombre' => 'Nuevo',
            'email' => 'nuevo@example.com',
            'password' => 'secreto123',
            'rol' => 'admin',
            'estado' => 1,
        ])->assertSessionHasErrors('rol');

        $this->assertDatabaseMissing('users', ['email' => 'nuevo@example.com']);
    }

    public function test_non_admin_cannot_promote_self_or_edit_an_admin(): void
    {
        $gestor = $this->staff(['usuarios']);
        $admin = $this->admin();

        $this->actingAs($gestor)->put("/usuarios/{$gestor->id}", [
            'nombre' => $gestor->nombre,
            'email' => $gestor->email,
            'rol' => 'admin',
            'estado' => 1,
        ])->assertSessionHasErrors('rol');
        $this->assertSame('agente', $gestor->fresh()->rol);

        $this->actingAs($gestor)->put("/usuarios/{$admin->id}", [
            'nombre' => 'X',
            'email' => $admin->email,
            'rol' => 'admin',
            'estado' => 1,
            'password' => 'tomado123',
        ])->assertForbidden();

        $this->actingAs($gestor)->delete("/usuarios/{$admin->id}")->assertForbidden();
        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_can_still_create_admins(): void
    {
        $this->actingAs($this->admin())->post('/usuarios', [
            'nombre' => 'Otro admin',
            'email' => 'otro@example.com',
            'password' => 'secreto123',
            'rol' => 'admin',
            'estado' => 1,
        ])->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('users', ['email' => 'otro@example.com', 'rol' => 'admin']);
    }

    public function test_cannot_delete_self_or_last_admin(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete("/usuarios/{$admin->id}")->assertSessionHasErrors('usuario');
        $this->assertNotNull($admin->fresh());

        $this->actingAs($admin)->put("/usuarios/{$admin->id}", [
            'nombre' => $admin->nombre,
            'email' => $admin->email,
            'rol' => 'agente',
            'permisos' => ['clientes'],
            'estado' => 1,
        ])->assertSessionHasErrors('rol');
        $this->assertSame('admin', $admin->fresh()->rol);
    }

    // --- Login ---

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'u@example.com', 'rol' => 'agente', 'permisos' => ['clientes']]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'u@example.com', 'password' => 'mala'])->assertRedirect();
        }

        $this->post('/login', ['email' => 'u@example.com', 'password' => 'mala'])->assertStatus(429);
    }

    public function test_inactive_message_only_with_correct_password(): void
    {
        User::factory()->create(['email' => 'off@example.com', 'estado' => false, 'rol' => 'agente', 'permisos' => ['clientes']]);

        $this->from('/login')->post('/login', ['email' => 'off@example.com', 'password' => 'mala']);
        $this->assertStringContainsString('credenciales', session('errors')->first('email'));

        $this->from('/login')->post('/login', ['email' => 'off@example.com', 'password' => 'password']);
        $this->assertStringContainsString('desactivada', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_public_tracking_is_rate_limited(): void
    {
        config(['services.tracking.provider' => 'none']);

        for ($i = 0; $i < 20; $i++) {
            $this->get('/rastreo')->assertOk();
        }

        $this->get('/rastreo')->assertStatus(429);
    }

    // --- Permisos y portal ---

    public function test_module_permission_blocks_other_modules(): void
    {
        $basico = $this->staff(['inventario'], 'basico');

        $this->actingAs($basico)->get('/facturacion')->assertForbidden();
        $this->actingAs($basico)->get('/usuarios')->assertForbidden();
        $this->actingAs($basico)->get('/cargotrack')->assertForbidden();
    }

    public function test_portal_client_cannot_see_other_clients_invoices(): void
    {
        $mio = $this->cliente('Mio');
        $ajeno = $this->cliente('Ajeno');

        $facturaAjena = Facturacion::create([
            'cliente_id' => $ajeno->id,
            'fecha_factura' => now()->toDateString(),
            'monto_total' => 100,
            'moneda' => 'USD',
            'estado_pago' => 'pendiente',
        ]);

        $usuario = User::factory()->create([
            'rol' => 'cliente',
            'cliente_id' => $mio->id,
            'permisos' => null,
        ]);

        $this->actingAs($usuario)->get("/portal/facturas/{$facturaAjena->id}")->assertNotFound();
        $this->actingAs($usuario)->get("/portal/facturas/{$facturaAjena->id}/pdf")->assertNotFound();
        $this->actingAs($usuario)->get('/clientes')->assertRedirect(route('portal.home'));
    }

    // --- Datos sensibles ---

    public function test_cargotrack_client_password_is_encrypted_at_rest(): void
    {
        $cliente = $this->cliente('Cifrado');

        $this->actingAs($this->admin())->put("/clientes/{$cliente->id}/cargotrack", [
            'cargotrack_username' => 'usuario',
            'cargotrack_password' => 'clave-secreta',
            'cargotrack_sync_enabled' => 1,
        ])->assertRedirect(route('clientes.show', $cliente->id));

        $raw = DB::table('clientes')->where('id', $cliente->id)->value('cargotrack_password');
        $this->assertNotSame('clave-secreta', $raw);
        $this->assertSame('clave-secreta', $cliente->fresh()->cargotrack_password);
        $this->assertArrayNotHasKey('cargotrack_password', $cliente->fresh()->toArray());
    }

    public function test_staff_with_dashboard_permission_sees_no_figures(): void
    {
        $cliente = $this->cliente('Secreto');
        $trabajador = $this->staff(['dashboard', 'inventario']);

        $this->actingAs($trabajador)->get('/')
            ->assertOk()
            ->assertSee('Tus módulos')
            ->assertDontSee('Secreto')
            ->assertDontSee('clientesData', false)
            ->assertDontSee('Estadísticas por cliente');

        $this->actingAs($trabajador)
            ->getJson('/dashboard/estadisticas-paquetes-cliente?cliente_id='.$cliente->id)
            ->assertForbidden();
        $this->actingAs($trabajador)
            ->getJson('/dashboard/estadisticas-paquetes')
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->getJson('/dashboard/estadisticas-paquetes-cliente?cliente_id=todos')
            ->assertOk()
            ->assertJsonPath('todos_clientes', true);
    }

    public function test_dashboard_and_cargotrack_pages_render_for_admin(): void
    {
        $admin = $this->admin();
        $this->cliente('Uno');

        $this->actingAs($admin)->get('/')->assertOk()->assertViewHas('clientesData');
        $this->actingAs($admin)->get('/cargotrack')->assertOk()->assertSee('Sincronizar ahora');
    }
}
