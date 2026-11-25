<?php

namespace Tests\Feature;

use App\Contracts\TrackingProvider;
use App\Models\Cliente;
use App\Models\Facturacion;
use App\Models\Inventario;
use App\Models\Servicio;
use App\Models\User;
use App\Services\Tracking\NullTrackingProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Lo que ve el cliente: facturado = Entregado; ingresado sin factura = En oficina. */
class EstadoClienteTest extends TestCase
{
    use RefreshDatabase;

    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(TrackingProvider::class, new NullTrackingProvider);
        $this->cliente = Cliente::create([
            'nombre_completo' => 'Cliente Rastreo',
            'correo' => 'rastreo@example.com',
            'telefono' => '0000',
            'direccion' => 'Dirección',
            'tipo_cliente' => 'normal',
            'fecha_registro' => now()->toDateString(),
        ]);
    }

    private function paquete(string $tracking, ?int $facturaId, string $estado = 'recibido'): Inventario
    {
        return Inventario::create([
            'cliente_id' => $this->cliente->id,
            'servicio_id' => Servicio::firstOrCreate(['tipo_servicio' => 'Aéreo'])->id,
            'factura_id' => $facturaId,
            'peso_lb' => 2,
            'monto_calculado' => 10,
            'fecha_ingreso' => now()->toDateString(),
            'estado' => $estado,
            'tracking_codigo' => $tracking,
        ]);
    }

    private function factura(): int
    {
        return Facturacion::create([
            'cliente_id' => $this->cliente->id,
            'fecha_factura' => now()->toDateString(),
            'monto_total' => 10,
            'moneda' => 'USD',
            'estado_pago' => 'pendiente',
        ])->id;
    }

    public function test_rastreo_paquete_sin_factura_sale_en_oficina(): void
    {
        $this->paquete('TBA111222333', null);

        $this->get('/rastreo?tracking=TBA111222333')
            ->assertOk()
            ->assertSee('Estado actual')
            ->assertSee('En oficina')
            ->assertDontSee('No encontramos ese código');
    }

    public function test_rastreo_paquete_facturado_sale_entregado_aunque_diga_recibido(): void
    {
        $this->paquete('TBA444555666', $this->factura(), 'recibido');

        $this->get('/rastreo?tracking=TBA444555666')
            ->assertOk()
            ->assertSee('Tu paquete fue entregado.');
    }

    public function test_portal_muestra_y_filtra_por_estado_cliente(): void
    {
        $this->paquete('TBAOFICINA1', null);
        $this->paquete('TBAFACTURA1', $this->factura());
        $usuario = User::factory()->create(['rol' => 'cliente', 'cliente_id' => $this->cliente->id, 'permisos' => null]);

        $this->actingAs($usuario)->get('/portal/paquetes?estado=en_oficina')
            ->assertOk()->assertSee('TBAOFICINA1')->assertDontSee('TBAFACTURA1');
        $this->actingAs($usuario)->get('/portal/paquetes?estado=entregado')
            ->assertOk()->assertSee('TBAFACTURA1')->assertDontSee('TBAOFICINA1');
        $this->actingAs($usuario)->get('/portal')->assertOk()->assertSee('En oficina')->assertSee('Entregado');
    }
}
