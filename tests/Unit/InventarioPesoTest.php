<?php

namespace Tests\Unit;

use App\Models\Inventario;
use PHPUnit\Framework\TestCase;

class InventarioPesoTest extends TestCase
{
    public function test_format_peso_keeps_up_to_four_decimals_without_trailing_zeros(): void
    {
        $this->assertSame('1.2345', Inventario::formatPeso(1.2345));
        $this->assertSame('2.5', Inventario::formatPeso(2.5000));
        $this->assertSame('3', Inventario::formatPeso(3));
        $this->assertSame('0', Inventario::formatPeso(null));
    }

    public function test_monto_rounds_money_to_two_decimals_from_precise_weight(): void
    {
        $this->assertSame(3.09, Inventario::calcularMonto(1.2345, 2.50));
        $this->assertSame(2.47, Inventario::calcularMonto(1.2345, 2));
        $this->assertSame(10.00, Inventario::calcularMonto(4, 2.5));
    }
}
