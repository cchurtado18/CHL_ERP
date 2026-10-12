<?php

namespace Tests\Unit;

use App\Support\TrackingCode;
use PHPUnit\Framework\TestCase;

class TrackingCodeTest extends TestCase
{
    public function test_normalize_strips_wrh_spaces_and_punctuation(): void
    {
        $this->assertSame('6521593', TrackingCode::normalize(' WRH-6521593 '));
        $this->assertSame('SPXMIA05675207852732', TrackingCode::normalize('spxmia05675207852732'));
        $this->assertSame('1ZF96V330346312809', TrackingCode::normalize('1Z F96V 3303 4631 2809'));
    }

    public function test_variants_include_raw_and_normalized_forms(): void
    {
        $variants = TrackingCode::variants('WRH 6521593');

        $this->assertContains('WRH 6521593', $variants);
        $this->assertContains('6521593', $variants);
    }

    public function test_numeric_guia_detection(): void
    {
        $this->assertTrue(TrackingCode::isNumericGuia('6521593'));
        $this->assertTrue(TrackingCode::isNumericGuia('WRH-6521593'));
        $this->assertFalse(TrackingCode::isNumericGuia('SPXMIA05675207852732'));
    }
}
