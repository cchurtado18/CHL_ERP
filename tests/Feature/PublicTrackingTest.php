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
}
