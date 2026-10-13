<?php

namespace App\Console\Commands;

use App\Contracts\TrackingProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TrackingProbeCommand extends Command
{
    protected $signature = 'tracking:probe {code : Tracking o guía a consultar}';

    protected $description = 'Diagnostica la consulta pública a gc.cargotrack.net/m/track.asp';

    public function handle(TrackingProvider $provider): int
    {
        $code = trim((string) $this->argument('code'));
        $url = rtrim((string) config('services.cargotrack.base_url'), '/').
            (string) config('services.cargotrack.track_path', '/m/track.asp');

        $this->line('provider='.$provider->driver());
        $this->line('enabled='.($provider->enabled() ? 'yes' : 'no'));
        $this->line('url='.$url);
        $this->line('code='.$code);

        $started = microtime(true);
        try {
            $response = Http::timeout(20)
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                    'connect_timeout' => 8,
                    'allow_redirects' => true,
                ])
                ->asForm()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0',
                    'Referer' => $url,
                    'Origin' => rtrim((string) config('services.cargotrack.base_url'), '/'),
                ])
                ->post($url, [
                    'track' => $code,
                    'action2' => 'process',
                ]);

            $seconds = round(microtime(true) - $started, 2);
            $body = $response->body();
            $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($body)));

            $this->line('http='.$response->status());
            $this->line('seconds='.$seconds);
            $this->line('bytes='.strlen($body));
            $this->line('searching='.(stripos($body, 'Searching for') !== false ? 'yes' : 'no'));
            $this->line('not_found='.(stripos($body, 'NOT FOUND') !== false ? 'yes' : 'no'));
            $this->line('snippet='.mb_substr($text, 0, 240));
        } catch (\Throwable $e) {
            $this->error('http_exception='.$e->getMessage());
            $this->line('seconds='.round(microtime(true) - $started, 2));
        }

        $parsed = $provider->lookup($code);
        if ($parsed) {
            $this->info('lookup=OK status='.($parsed['status_raw'] ?? '').' wrh='.($parsed['wrh'] ?? '').' track='.($parsed['tracking_number'] ?? ''));
        } else {
            $this->warn('lookup=NULL error='.($provider->lastError() ?: 'sin error (CargoTrack dijo que no existe)'));
        }

        return self::SUCCESS;
    }
}
