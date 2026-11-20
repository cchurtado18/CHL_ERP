<?php

namespace App\Services\Tracking;

use App\Models\CargotrackAgencia;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Cliente admin autenticado: lista de warehouse + detalle/documento por guía.
 */
class CargoTrackAdminClient
{
    /** @var array<string, string> */
    protected array $cookieJar = [];

    protected bool $loggedIn = false;

    protected ?string $lastLoginError = null;

    public function __construct(protected CargoTrackProvider $provider) {}

    public function lastLoginError(): ?string
    {
        return $this->lastLoginError;
    }

    public function login(string $username, string $password): bool
    {
        $this->cookieJar = [];
        $this->loggedIn = false;
        $this->lastLoginError = null;

        $username = trim($username);
        $password = (string) $password;

        if ($username === '' || $password === '') {
            $this->lastLoginError = 'Usuario o contraseña vacíos.';

            return false;
        }

        $base = rtrim((string) config('services.cargotrack.base_url'), '/');
        $timeout = (int) config('services.cargotrack.timeout', 20);

        // Un solo intento principal para no disparar bloqueos por fuerza bruta.
        // Si falla por credenciales incorrectas, NO reintentar en otras URLs.
        $attempts = [
            ['url' => $base.'/login.asp', 'fields' => ['user' => $username, 'password' => $password, 'action' => 'login', 'language' => '']],
            ['url' => $base.'/default.asp', 'fields' => ['user' => $username, 'password' => $password, 'action' => 'login']],
        ];

        foreach ($attempts as $index => $attempt) {
            $response = $this->http($timeout)
                ->asForm()
                ->withHeaders(['Referer' => $attempt['url']])
                ->post($attempt['url'], $attempt['fields']);

            $this->mergeCookies($response->cookies());

            if (! $response->successful()) {
                continue;
            }

            if ($this->loginLooksFailed($response->body())) {
                $this->lastLoginError = 'CargoTrack rechazó el usuario/contraseña. '
                    .'Si falló varias veces, GC puede haber bloqueado temporalmente la cuenta o la IP. '
                    .'Espera y vuelve a intentar desde el navegador; el usuario de login no es necesariamente CHLOGIX.';

                return false;
            }

            // Confirmar con una sola lectura del home (evitar ráfaga de requests).
            $home = $this->http($timeout)->get($base.'/default.asp');
            $this->mergeCookies($home->cookies());
            $body = $home->body();

            if ($this->loginLooksFailed($body)) {
                $this->lastLoginError = 'CargoTrack rechazó el usuario/contraseña tras el login.';

                return false;
            }

            $isNotFoundPage = stripos($body, '404.') !== false || stripos($body, 'HTTP Error 404') !== false;
            $looksAuthenticated = ! $isNotFoundPage && (
                stripos($body, 'logout') !== false
                || stripos($body, 'log out') !== false
                || stripos($body, 'Consignee') !== false
                || (
                    stripos($body, 'Warehouse') !== false
                    && stripos($body, 'Shipper') !== false
                )
                || (
                    stripos($body, 'Forgot Password') === false
                    && stripos($body, 'name="login"') === false
                    && stripos($body, 'name="username"') === false
                    && strlen($body) > 2500
                )
            );

            if ($looksAuthenticated && $this->hasAspSession()) {
                $this->loggedIn = true;
                $this->lastLoginError = null;

                return true;
            }

            // Si el primer endpoint respondió 200 sin error pero no autenticó, probar el siguiente una vez.
            if ($index === 0) {
                continue;
            }
        }

        $this->lastLoginError = 'No se pudo confirmar la sesión en CargoTrack. Espera unos minutos si hubo bloqueo y verifica las credenciales en el navegador.';

        return false;
    }

    /**
     * Lista paquetes del warehouse autenticado.
     *
     * @return list<array<string, mixed>>
     */
    public function listWarehousePackages(): array
    {
        if (! $this->loggedIn) {
            return [];
        }

        $base = rtrim((string) config('services.cargotrack.base_url'), '/');
        $timeout = (int) config('services.cargotrack.timeout', 20);
        $paths = array_values(array_unique(array_filter(array_merge(
            (array) config('services.cargotrack.package_list_paths', []),
            [
                '/default.asp',
                '/warehouse.asp',
                '/wrh.asp',
                '/shipping.asp',
                '/m/default.asp',
            ]
        ))));

        $packages = [];
        $visited = [];

        foreach ($paths as $path) {
            $url = $base.'/'.ltrim((string) $path, '/');
            if (isset($visited[$url])) {
                continue;
            }
            $visited[$url] = true;

            try {
                $response = $this->http($timeout)->get($url);
                $this->mergeCookies($response->cookies());
                if (! $response->successful()) {
                    continue;
                }

                $html = $this->toUtf8($response->body());
                if (stripos($html, '404.') !== false && stripos($html, 'Not Found') !== false) {
                    continue;
                }

                foreach ($this->extractAspLinks($html) as $link) {
                    $abs = $this->absolutize($base, $link);
                    $p = parse_url($abs, PHP_URL_PATH);
                    if ($p && ! isset($visited[$base.$p])) {
                        $paths[] = $p;
                    }
                }

                $parsed = $this->parseWarehouseListHtml($html, $base);
                if ($parsed !== []) {
                    foreach ($parsed as $row) {
                        $key = (string) ($row['warehouse'] ?? '');
                        if ($key === '') {
                            continue;
                        }
                        $packages[$key] = $row;
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('CargoTrack admin list page skip', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return array_values($packages);
    }

    /**
     * Enriquece con lookup público (tracking largo, eventos, estado).
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function enrichWithLookup(array $row): array
    {
        $code = (string) ($row['warehouse'] ?? $row['tracking_codigo'] ?? '');
        if ($code === '') {
            return $row;
        }

        $detail = $this->provider->lookup($code);
        if (! $detail) {
            return $row;
        }

        $row['tracking_codigo'] = $detail['tracking_number'] ?? ($row['tracking_codigo'] ?? null);
        // wrh del lookup debe ser la guía de 6 dígitos
        if (! empty($detail['wrh']) && preg_match('/^\d{5,9}$/', (string) $detail['wrh'])) {
            $row['warehouse'] = $detail['wrh'];
        }
        $row['estado'] = $detail['status'] ?? ($row['estado'] ?? null);
        $row['status_raw'] = $detail['status_raw'] ?? ($row['status_raw'] ?? null);
        $row['peso_lb'] = $detail['weight'] ?? ($row['peso_lb'] ?? null);
        $row['descripcion'] = $detail['description'] ?? ($row['descripcion'] ?? null);
        $row['events'] = $detail['events'] ?? [];
        if (! empty($detail['pickup_date']) && empty($row['fecha_warehouse'])) {
            try {
                $row['fecha_warehouse'] = \Carbon\Carbon::parse($detail['pickup_date'])->toDateString();
            } catch (\Throwable) {
            }
        }

        return $row;
    }

    /**
     * Intenta descargar PDF/imagen del detalle del paquete.
     *
     * @return array{path:string,mime:string,url:string}|null
     */
    public function fetchDocument(string $warehouse, ?string $detailUrl = null): ?array
    {
        if (! $this->loggedIn) {
            return null;
        }

        $base = rtrim((string) config('services.cargotrack.base_url'), '/');
        $timeout = (int) config('services.cargotrack.timeout', 20);
        $warehouse = trim($warehouse);

        $candidates = array_filter([
            $detailUrl,
            $base.'/print.asp?wrh='.urlencode($warehouse),
            $base.'/print.asp?warehouse='.urlencode($warehouse),
            $base.'/label.asp?wrh='.urlencode($warehouse),
            $base.'/labels.asp?wrh='.urlencode($warehouse),
            $base.'/detail.asp?wrh='.urlencode($warehouse),
            $base.'/warehouse.asp?wrh='.urlencode($warehouse),
            $base.'/wrh.asp?wrh='.urlencode($warehouse),
            $base.'/m/track.asp',
        ]);

        foreach ($candidates as $url) {
            try {
                if (str_ends_with(strtolower(parse_url((string) $url, PHP_URL_PATH) ?? ''), 'track.asp')) {
                    $response = $this->http($timeout)->asForm()->post((string) $url, [
                        'track' => $warehouse,
                        'action2' => 'process',
                    ]);
                } else {
                    $response = $this->http($timeout)->get((string) $url);
                }
                $this->mergeCookies($response->cookies());

                if (! $response->successful()) {
                    continue;
                }

                $contentType = strtolower((string) $response->header('Content-Type'));
                $body = $response->body();

                if (str_contains($contentType, 'pdf') || str_starts_with($body, '%PDF')) {
                    return $this->storeDocument($warehouse, $body, 'application/pdf', (string) $url, 'pdf');
                }

                if (str_contains($contentType, 'image/')) {
                    $ext = str_contains($contentType, 'png') ? 'png' : (str_contains($contentType, 'gif') ? 'gif' : 'jpg');

                    return $this->storeDocument($warehouse, $body, $contentType, (string) $url, $ext);
                }

                // HTML: buscar enlaces a pdf/imagen/print
                $html = $this->toUtf8($body);
                if (preg_match_all('/(?:href|src)=["\']([^"\']+\.(?:pdf|jpe?g|png|gif)(?:\?[^"\']*)?)["\']/i', $html, $m)) {
                    foreach ($m[1] as $href) {
                        $abs = $this->absolutize($base, html_entity_decode($href, ENT_QUOTES | ENT_HTML5));
                        $file = $this->http($timeout)->get($abs);
                        $this->mergeCookies($file->cookies());
                        if (! $file->successful()) {
                            continue;
                        }
                        $ct = strtolower((string) $file->header('Content-Type'));
                        $bin = $file->body();
                        if (str_contains($ct, 'pdf') || str_starts_with($bin, '%PDF') || str_ends_with(strtolower($abs), '.pdf')) {
                            return $this->storeDocument($warehouse, $bin, 'application/pdf', $abs, 'pdf');
                        }
                        if (str_contains($ct, 'image/') || preg_match('/\.(jpe?g|png|gif)(\?|$)/i', $abs)) {
                            $ext = 'jpg';
                            if (preg_match('/\.(png|gif|jpe?g)(\?|$)/i', $abs, $em)) {
                                $ext = strtolower($em[1]) === 'jpeg' ? 'jpg' : strtolower($em[1]);
                            }

                            return $this->storeDocument($warehouse, $bin, $ct ?: 'image/'.$ext, $abs, $ext);
                        }
                    }
                }

                if (preg_match_all('/(?:href|location\.href)=["\']?([^"\'>\s]*(?:print|label|pdf)[^"\'>\s]*)/i', $html, $pm)) {
                    foreach ($pm[1] as $href) {
                        $abs = $this->absolutize($base, html_entity_decode($href, ENT_QUOTES | ENT_HTML5));
                        if ($abs === $url) {
                            continue;
                        }
                        $nested = $this->fetchDocument($warehouse, $abs);
                        if ($nested) {
                            return $nested;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('CargoTrack document fetch skip', [
                    'warehouse' => $warehouse,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Parser de la tabla warehouse (Dest, Warehouse, Date, Shipper, Consignee, Pieces, Weight, Volume…).
     *
     * @return list<array<string, mixed>>
     */
    public function parseWarehouseListHtml(string $html, string $base = ''): array
    {
        $rows = [];

        // Filas de tabla genéricas
        if (! preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $trMatches)) {
            return [];
        }

        foreach ($trMatches[1] as $trHtml) {
            if (stripos($trHtml, '<th') !== false) {
                continue;
            }
            if (! preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $trHtml, $tdMatches)) {
                continue;
            }

            $cells = [];
            foreach ($tdMatches[1] as $td) {
                $cells[] = $this->normalizeCell($td);
            }

            if (count($cells) < 5) {
                continue;
            }

            // Buscar warehouse (6 dígitos) en las celdas
            $warehouse = null;
            $warehouseIdx = null;
            foreach ($cells as $i => $cell) {
                if (preg_match('/\b(\d{6,9})\b/', $cell['text'], $wm)) {
                    $warehouse = $wm[1];
                    $warehouseIdx = $i;
                    break;
                }
            }
            if (! $warehouse) {
                continue;
            }

            // Modalidad por iconos en el HTML de la fila
            $modalidad = null;
            if (preg_match('/airplane|plane|aereo|air/i', $trHtml)) {
                $modalidad = 'aereo';
            } elseif (preg_match('/anchor|ship|maritim|ocean|boat/i', $trHtml)) {
                $modalidad = 'maritimo';
            }

            // Heurística de columnas relativa al warehouse (como en la captura)
            // [icons…] Dest | Warehouse | Date | Shipper | Consignee | Pieces | Weight | Volume | Value
            $date = null;
            $shipper = null;
            $agencia = null;
            $consignee = null;
            $piezas = null;
            $peso = null;
            $volumen = null;
            $valor = null;

            $after = array_slice($cells, (int) $warehouseIdx + 1);
            if (isset($after[0]) && preg_match('/\d{1,2}\/\d{1,2}\/\d{2,4}/', $after[0]['text'])) {
                $date = $after[0]['text'];
            }
            if (isset($after[1])) {
                $shipper = $after[1]['text'] !== '' ? $after[1]['text'] : null;
            }
            if (isset($after[2])) {
                $lines = $after[2]['lines'];
                if (count($lines) >= 2) {
                    $agencia = CargotrackAgencia::normalizeCodigo($lines[0]);
                    $consignee = $lines[1];
                } elseif (count($lines) === 1) {
                    $agencia = CargotrackAgencia::normalizeCodigo($lines[0]);
                    $consignee = $lines[0];
                }
                // Preferir links azules: ya están en lines por <br> o <a>
            }
            if (isset($after[3]) && preg_match('/\d+/', $after[3]['text'], $pm)) {
                $piezas = (int) $pm[0];
            }
            if (isset($after[4])) {
                // Weight puede ser "0.8/3" → tomar primer número
                if (preg_match('/([\d.,]+)/', $after[4]['text'], $wm)) {
                    $peso = (float) str_replace(',', '', $wm[1]);
                }
            }
            if (isset($after[5]) && preg_match('/([\d.,]+)/', $after[5]['text'], $vm)) {
                $volumen = (float) str_replace(',', '', $vm[1]);
            }
            if (isset($after[6]) && preg_match('/([\d.,]+)/', $after[6]['text'], $valm)) {
                $valor = (float) str_replace(',', '', $valm[1]);
            }

            // Detail / print links in row
            $detailUrl = null;
            if (preg_match('/(?:href|onClick)=["\']?([^"\'>\s]+\.asp[^"\'>\s]*)/i', $trHtml, $lm)) {
                $detailUrl = $this->absolutize($base !== '' ? $base : rtrim((string) config('services.cargotrack.base_url'), '/'), html_entity_decode($lm[1], ENT_QUOTES | ENT_HTML5));
            }

            $fecha = null;
            if ($date) {
                try {
                    $fecha = \Carbon\Carbon::parse($date)->toDateString();
                } catch (\Throwable) {
                    $fecha = null;
                }
            }

            $rows[] = [
                'warehouse' => $warehouse,
                'tracking_codigo' => null,
                'agencia_codigo' => $agencia,
                'consignee_nombre' => $consignee,
                'shipper' => $shipper,
                'modalidad' => $modalidad,
                'fecha_warehouse' => $fecha,
                'piezas' => $piezas,
                'peso_lb' => $peso,
                'volumen' => $volumen,
                'valor' => $valor,
                'detail_url' => $detailUrl,
                'raw_cells' => array_map(fn ($c) => $c['text'], $cells),
            ];
        }

        return $rows;
    }

    /**
     * @return array{text:string,lines:list<string>,html:string}
     */
    protected function normalizeCell(string $html): array
    {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5);
        // Separar por <br> / block tags
        $withBreaks = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $withBreaks = preg_replace('/<\/(?:p|div|a|span)>/i', "\n", $withBreaks) ?? $withBreaks;
        $text = trim(html_entity_decode(strip_tags($withBreaks), ENT_QUOTES | ENT_HTML5));
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n+/', "\n", $text) ?? $text;
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), fn ($l) => $l !== ''));

        return [
            'html' => $html,
            'text' => trim(preg_replace('/\s+/', ' ', $text) ?? $text),
            'lines' => $lines,
        ];
    }

    /**
     * @return array{path:string,mime:string,url:string}
     */
    protected function storeDocument(string $warehouse, string $binary, string $mime, string $url, string $ext): array
    {
        $path = 'cargotrack/docs/'.$warehouse.'/doc_'.now()->format('Ymd_His').'.'.$ext;
        Storage::disk('local')->put($path, $binary);

        return [
            'path' => $path,
            'mime' => $mime,
            'url' => $url,
        ];
    }

    protected function loginLooksFailed(string $body): bool
    {
        return stripos($body, 'User and/or password are incorrect') !== false
            || stripos($body, 'password are incorrect') !== false
            || (stripos($body, 'ui-state-error') !== false && stripos($body, 'Alert:') !== false);
    }

    protected function http(int $timeout): PendingRequest
    {
        $request = Http::timeout($timeout)
            ->withHeaders([
                'User-Agent' => (string) config(
                    'services.cargotrack.user_agent',
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ),
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9,es;q=0.8',
            ])
            ->withOptions(['allow_redirects' => true]);

        if ($this->cookieJar !== []) {
            $parts = [];
            foreach ($this->cookieJar as $name => $value) {
                $parts[] = $name.'='.$value;
            }
            $request = $request->withHeaders(['Cookie' => implode('; ', $parts)]);
        }

        return $request;
    }

    protected function mergeCookies(mixed $cookies): void
    {
        if (! is_iterable($cookies)) {
            return;
        }
        foreach ($cookies as $cookie) {
            $name = method_exists($cookie, 'getName') ? $cookie->getName() : null;
            $value = method_exists($cookie, 'getValue') ? $cookie->getValue() : null;
            if ($name && $value !== null) {
                $this->cookieJar[$name] = $value;
            }
        }
    }

    protected function hasAspSession(): bool
    {
        foreach (array_keys($this->cookieJar) as $name) {
            if (str_starts_with(strtoupper((string) $name), 'ASPSESSIONID')) {
                return true;
            }
        }

        return false;
    }

    protected function toUtf8(string $html): string
    {
        if (preg_match('/charset\s*=\s*["\']?iso-8859-1/i', $html) || ! mb_check_encoding($html, 'UTF-8')) {
            $converted = @mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');

            return is_string($converted) ? $converted : $html;
        }

        return $html;
    }

    /** @return list<string> */
    protected function extractAspLinks(string $html): array
    {
        $links = [];
        if (preg_match_all('/(?:href|location\.href|onClick)=["\']?([^"\'>\s]+\.asp[^"\'>\s]*)/i', $html, $m)) {
            foreach ($m[1] as $href) {
                $href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5);
                if (stripos($href, 'javascript:') === 0) {
                    continue;
                }
                $links[] = $href;
            }
        }

        return array_values(array_unique($links));
    }

    protected function absolutize(string $base, string $href): string
    {
        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            return $href;
        }
        if (str_starts_with($href, '//')) {
            return 'https:'.$href;
        }
        if (str_starts_with($href, '/')) {
            return $base.$href;
        }

        return $base.'/'.ltrim($href, './');
    }
}
