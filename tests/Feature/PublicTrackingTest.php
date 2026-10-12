<?php

namespace Tests\Feature;

use App\Contracts\TrackingProvider;
use App\Models\Cliente;
use App\Models\Inventario;
use App\Services\Tracking\CargoTrackProvider;
use App\Services\Tracking\NullTrackingProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(TrackingProvider::class, new NullTrackingProvider);
    }

    public function test_public_tracking_finds_guia_with_wrh_prefix(): void
    {
        $this->seedPaquete('6521593', 'SPXMIA05675207852732');

        $this->get('/rastreo?tracking='.urlencode('WRH-6521593'))
            ->assertOk()
            ->assertSee('6521593')
            ->assertSee('SPXMIA05675207852732')
            ->assertDontSee('No encontramos un paquete');
    }

    public function test_public_tracking_finds_tracking_with_extra_spaces(): void
    {
        $this->seedPaquete('662258', '1ZF96V330346312809');

        $this->get('/rastreo?tracking='.urlencode(' 1ZF96V330346312809 '))
            ->assertOk()
            ->assertSee('1ZF96V330346312809')
            ->assertDontSee('No encontramos un paquete');
    }

    public function test_public_tracking_finds_truncated_stored_tracking_by_prefix(): void
    {
        $this->seedPaquete('398234', 'SPXMIA056752078527');

        $this->get('/rastreo?tracking=SPXMIA05675207852732')
            ->assertOk()
            ->assertSee('SPXMIA056752078527')
            ->assertDontSee('No encontramos un paquete');
    }

    public function test_public_tracking_shows_not_found_for_unknown_code(): void
    {
        $this->get('/rastreo?tracking=NOEXISTE999')
            ->assertOk()
            ->assertSee('No encontramos un paquete');
    }

    public function test_public_tracking_shows_cargotrack_result_when_status_uses_font_tags(): void
    {
        $this->app->instance(TrackingProvider::class, new CargoTrackProvider);

        Http::fake(function ($request) {
            if ($request->method() === 'GET') {
                return Http::response('<html><form name="form1"></form></html>', 200);
            }

            return Http::response(
                <<<'HTML'
                <table class="ntextbig">
                  <tr><td><div align="center"><strong class="ntextbig">Searching for&nbsp;TBA999888777</strong></div></td></tr>
                  <tr><td><div align="center"><strong><font color="#006600">In Transit</font></strong></div></td></tr>
                  <tr><td>
                    <span class="ntextbig"><strong>778899</strong></span>
                    <br>
                    TBA999888777
                    <br>
                    <strong>1</strong>&nbsp;piece(s)&nbsp;with&nbsp;<strong>2.00&nbsp;lbs</strong>
                  </td></tr>
                </table>
                HTML,
                200
            );
        });

        $this->get('/rastreo?tracking=TBA999888777')
            ->assertOk()
            ->assertSee('TBA999888777')
            ->assertSee('778899')
            ->assertDontSee('No encontramos un paquete');
    }

    protected function seedPaquete(string $guia, string $tracking): Inventario
    {
        $cliente = Cliente::create([
            'nombre_completo' => 'Cliente Test',
            'correo' => 'test@example.com',
            'telefono' => '88888888',
            'tipo_cliente' => 'normal',
        ]);

        return Inventario::create([
            'cliente_id' => $cliente->id,
            'numero_guia' => $guia,
            'tracking_codigo' => $tracking,
            'estado' => 'recibido',
            'peso_lb' => 1,
            'fecha_ingreso' => now()->toDateString(),
        ]);
    }
}
