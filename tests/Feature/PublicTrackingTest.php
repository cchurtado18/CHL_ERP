<?php

namespace Tests\Feature;

use App\Contracts\TrackingProvider;
use App\Services\Tracking\CargoTrackProvider;
use App\Services\Tracking\NullTrackingProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicTrackingTest extends TestCase
{
    public function test_public_tracking_shows_not_found_for_unknown_code(): void
    {
        $this->app->instance(TrackingProvider::class, new NullTrackingProvider);

        $this->get('/rastreo?tracking=NOEXISTE999')
            ->assertOk()
            ->assertSee('No encontramos un paquete');
    }

    public function test_public_tracking_sends_code_to_cargotrack_and_shows_result(): void
    {
        $this->app->instance(TrackingProvider::class, new CargoTrackProvider);

        Http::fake(function ($request) {
            if ($request->method() === 'GET') {
                return Http::response('<html><form name="form1"></form></html>', 200);
            }

            $track = (string) ($request->data()['track'] ?? '');
            if (strcasecmp($track, 'TBA335262352996') !== 0) {
                return Http::response(
                    '<table><tr><td>Searching for&nbsp;'.$track.'</td></tr><tr><td><strong>NOT FOUND</strong></td></tr></table>',
                    200
                );
            }

            return Http::response(
                <<<'HTML'
                <table class="ntextbig">
                  <tr><td><div align="center"><strong class="ntextbig">Searching for&nbsp;TBA335262352996</strong></div></td></tr>
                  <tr><td><div align="center"><strong>IN TRANSIT</strong></div></td></tr>
                  <tr><td>
                    <span class="ntextbig"><strong>276462</strong></span>
                    <br>
                    tba335262352996
                    <br>
                    <strong>1</strong>&nbsp;piece(s)&nbsp;with&nbsp;<strong>2.8&nbsp;lbs</strong>
                  </td></tr>
                </table>
                HTML,
                200
            );
        });

        $this->get('/rastreo?tracking=tba3352623529960')
            ->assertOk()
            ->assertSee('276462')
            ->assertSee('IN TRANSIT')
            ->assertDontSee('No encontramos un paquete');
    }

    public function test_public_tracking_uses_reader_when_cargotrack_times_out(): void
    {
        $this->app->instance(TrackingProvider::class, new CargoTrackProvider);

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'r.jina.ai')) {
                return Http::response(
                    <<<'MD'
                    Searching for 4203316626129434608106244617510560
                    IN TRANSIT
                    Received on 10/14/2026 at 4:37:00 PM
                    279860
                    4203316626129434608106244617510560
                    1 piece(s) with 2.5 lbs
                    Tracking Events
                    Received
                    10/14/2026 4:38:40 PM
                    MD,
                    200
                );
            }

            throw new \Illuminate\Http\Client\ConnectionException(
                'cURL error 28: Failed to connect to gc.cargotrack.net port 443 after 8002 ms: Timeout was reached'
            );
        });

        $this->get('/rastreo?tracking=4203316626129434608106244617510560')
            ->assertOk()
            ->assertSee('279860')
            ->assertSee('IN TRANSIT')
            ->assertSee('2.50 lb')
            ->assertDontSee('No encontramos un paquete')
            ->assertDontSee('iframe', false);
    }

    public function test_parse_extracts_cargotrack_fields(): void
    {
        $html = <<<'HTML'
        <table class="ntextbig">
          <tr><td><div align="center"><strong class="ntextbig">Searching for&nbsp;4203316626129434608106244617510560</strong></div></td></tr>
          <tr><td><div align="center"><strong>IN TRANSIT</strong></div></td></tr>
          <tr><td>
            <span class="ntextbig"><strong>279860</strong></span>
            <br>
            4203316626129434608106244617510560
            <br>
            <strong>1</strong>&nbsp;piece(s)&nbsp;with&nbsp;<strong>2.5&nbsp;lbs</strong>
          </td></tr>
        </table>
        HTML;

        $this->postJson(route('public.tracking.parse'), [
            'code' => '4203316626129434608106244617510560',
            'html' => $html,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('remoto.wrh', '279860')
            ->assertJsonPath('remoto.status_raw', 'IN TRANSIT');
    }
}
