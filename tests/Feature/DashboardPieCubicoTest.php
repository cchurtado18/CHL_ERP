<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Inventario;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPieCubicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_clave_tipo_normaliza_nombres_reales(): void
    {
        $this->assertSame('pie_cubico', Servicio::claveTipo('Pie Cúbico'));
        $this->assertSame('maritimo', Servicio::claveTipo('Marítimo'));
        $this->assertSame('aereo', Servicio::claveTipo('Aéreo'));
    }

    public function test_filtro_pie_cubico_devuelve_paquetes(): void
    {
        $admin = User::factory()->create(['rol' => 'admin', 'permisos' => null]);
        $cliente = Cliente::create([
            'nombre_completo' => 'Cliente Pie',
            'correo' => 'pie@example.com',
            'telefono' => '0000',
            'direccion' => 'Dirección',
            'tipo_cliente' => 'normal',
            'fecha_registro' => now()->toDateString(),
        ]);
        $pie = Servicio::create(['tipo_servicio' => 'Pie Cúbico']);
        $aereo = Servicio::create(['tipo_servicio' => 'Aéreo']);

        foreach ([[$pie, 10, 50], [$pie, 5, 25], [$aereo, 3, 9]] as [$s, $lb, $monto]) {
            Inventario::create([
                'cliente_id' => $cliente->id,
                'servicio_id' => $s->id,
                'peso_lb' => $lb,
                'monto_calculado' => $monto,
                'fecha_ingreso' => now()->toDateString(),
                'estado' => 'recibido',
            ]);
        }

        // Un cliente
        $this->actingAs($admin)
            ->getJson("/dashboard/estadisticas-paquetes-cliente?cliente_id={$cliente->id}&tipo_servicio=pie_cubico")
            ->assertOk()
            ->assertJsonPath('total', 2);

        // Todos los clientes
        $r = $this->actingAs($admin)
            ->getJson('/dashboard/estadisticas-paquetes-cliente?cliente_id=todos&tipo_servicio=pie_cubico')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('por_tipo.pie_cubico.paquetes', 2);
        $this->assertEquals(75, $r->json('dinero'));

        // Datos embebidos en la página (filtro sin rango de fechas)
        $this->actingAs($admin)->get('/')
            ->assertOk()
            ->assertViewHas('clientesData', fn ($d) => $d[$cliente->id]['paquetes_pie_cubico'] === 2);
    }
}
