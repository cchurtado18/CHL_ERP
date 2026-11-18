<?php

namespace Tests\Unit;

use App\Models\Inventario;
use PHPUnit\Framework\TestCase;

class InventarioPesoTest extends TestCase
{
    public function test_format_peso_stays_at_two_decimals_unless_extra_were_entered(): void
    {
        $this->assertSame('2.50', Inventario::formatPeso(2.5000));
        $this->assertSame('1.23', Inventario::formatPeso(1.23));
        $this->assertSame('3.00', Inventario::formatPeso(3));
        $this->assertSame('0.00', Inventario::formatPeso(null));
        $this->assertSame('1.234', Inventario::formatPeso(1.2340));
        $this->assertSame('1.2345', Inventario::formatPeso(1.2345));
    }

    public function test_monto_rounds_money_to_two_decimals_from_precise_weight(): void
    {
        $this->assertSame(3.09, Inventario::calcularMonto(1.2345, 2.50));
        $this->assertSame(2.47, Inventario::calcularMonto(1.2345, 2));
        $this->assertSame(10.00, Inventario::calcularMonto(4, 2.5));
    }

    public function test_monto_facturable_uses_inventory_line_and_same_rounding(): void
    {
        $conMonto = new Inventario(['peso_lb' => 1.2345, 'tarifa_manual' => 2.50, 'monto_calculado' => 3.09]);
        $this->assertSame(3.09, $conMonto->montoFacturable());

        $sinMonto = new Inventario(['peso_lb' => 1.2345, 'tarifa_manual' => 2.50, 'monto_calculado' => null]);
        $this->assertSame(3.09, $sinMonto->montoFacturable());
    }

    public function test_invoice_total_is_sum_of_rounded_inventory_lines(): void
    {
        $lineas = [
            Inventario::calcularMonto(1.2345, 2.50),
            Inventario::calcularMonto(1.2345, 2),
        ];
        $delivery = 1.00;
        $total = round(array_sum($lineas) + $delivery, 2, PHP_ROUND_HALF_UP);

        $this->assertSame(3.09, $lineas[0]);
        $this->assertSame(2.47, $lineas[1]);
        $this->assertSame(6.56, $total);
    }
}
