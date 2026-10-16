<?php

namespace Tests\Unit;

use App\Services\Tracking\CargoTrackProvider;
use Tests\TestCase;

class CargoTrackParseTest extends TestCase
{
    public function test_not_found_page_returns_null(): void
    {
        $html = <<<'HTML'
        <table class="ntextbig">
          <tr><td><div align="center"><strong class="ntextbig">Searching for&nbsp;6521593</strong></div></td></tr>
          <tr><td><div align="center"><strong>NOT FOUND</strong></div></td></tr>
        </table>
        HTML;

        $this->assertNull($this->parser()->parse($html, '6521593'));
    }

    public function test_found_status_inside_font_tag_is_detected(): void
    {
        $html = <<<'HTML'
        <table class="ntextbig">
          <tr><td><div align="center"><strong class="ntextbig">Searching for&nbsp;TBA123456789</strong></div></td></tr>
          <tr><td><div align="center"><strong><font color="#006600">In Transit</font></strong></div></td></tr>
          <tr><td>
            <span class="ntextbig"><strong>664939</strong></span>
            <br>
            TBA123456789
            <br>
            <strong>1</strong>&nbsp;piece(s)&nbsp;with&nbsp;<strong>3.40&nbsp;lbs</strong>
          </td></tr>
        </table>
        HTML;

        $parsed = $this->parser()->parse($html, 'TBA123456789');

        $this->assertNotNull($parsed);
        $this->assertSame('In Transit', $parsed['status_raw']);
        $this->assertSame('en_transito', $parsed['status']);
        $this->assertSame('664939', $parsed['wrh']);
        $this->assertSame('TBA123456789', $parsed['tracking_number']);
    }

    public function test_does_not_treat_unrelated_not_found_text_as_missing_package(): void
    {
        $html = <<<'HTML'
        <table class="ntextbig">
          <tr><td><div align="center"><strong class="ntextbig">Searching for&nbsp;TBA123456789</strong></div></td></tr>
          <tr><td><div align="center"><strong>Received</strong></div></td></tr>
        </table>
        <footer>Need help? See our Not Found FAQ.</footer>
        HTML;

        $parsed = $this->parser()->parse($html, 'TBA123456789');

        $this->assertNotNull($parsed);
        $this->assertSame('Received', $parsed['status_raw']);
        $this->assertSame('recibido', $parsed['status']);
    }

    public function test_jina_markdown_extracts_wrh_weight_and_status(): void
    {
        $markdown = <<<'MD'
        Searching for 4203316626129434608106244617510560
        IN TRANSIT
        Received on 10/14/2026 at 4:37:00 PM
        279860
        4203316626129434608106244617510560
        1 piece(s) with 2.5 lbs
        Tracking Events
        Received
        10/14/2026 4:38:40 PM
        MD;

        $parsed = $this->parser()->parse($markdown, '4203316626129434608106244617510560');

        $this->assertNotNull($parsed);
        $this->assertSame('IN TRANSIT', $parsed['status_raw']);
        $this->assertSame('279860', $parsed['wrh']);
        $this->assertSame(2.5, $parsed['weight']);
        $this->assertSame('Received', $parsed['events'][0]['status'] ?? null);
    }

    protected function parser(): CargoTrackProvider
    {
        return new class extends CargoTrackProvider
        {
            public function parse(string $html, string $code): ?array
            {
                return $this->parseScrapedHtml($html, $code);
            }
        };
    }
}
