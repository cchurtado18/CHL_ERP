<?php

namespace App\Services\Tracking;

use App\Contracts\TrackingProvider;
use App\Support\TrackingCode;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CargoTrackProvider implements TrackingProvider
{
    protected ?string $lastError = null;

    public function enabled(): bool
    {
        return rtrim((string) config('services.cargotrack.base_url'), '/') !== '';
    }

    public function driver(): string
    {
        return 'cargotrack';
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public function lookup(string $code): ?array
    {
        $this->lastError = null;
        $code = trim($code);
        if ($code === '' || ! $this->enabled()) {
            $this->lastError = $this->enabled() ? null : 'CargoTrack no está configurado.';

            return null;
        }

        foreach (TrackingCode::lookupVariants($code) as $variant) {
            $result = $this->lookupExact($variant);
            if ($result) {
                $this->lastError = null;

                return $result;
            }
        }

        return null;
    }

    protected function lookupExact(string $code): ?array
    {
        $base = rtrim((string) config('services.cargotrack.base_url'), '/');
        $path = (string) config('services.cargotrack.track_path', '/m/track.asp');
        $timeout = (int) config('services.cargotrack.timeout', 20);
        $url = $base.$path;

        try {
            $html = $this->scrapeTrack($url, $base, $code, $timeout, true);
            if ($html !== null) {
                $parsed = $this->parseScrapedHtml($html, $code);
                if ($parsed) {
                    $this->lastError = null;

                    return $parsed;
                }

                return null;
            }
        } catch (\Throwable $e) {
            $this->lastError = 'No se pudo consultar CargoTrack: '.$e->getMessage();
            Log::warning('CargoTrack scrape directo falló', [
                'code' => $code,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->lookupViaReader($code);
    }

    public function parseScrapedHtml(string $html, string $code): ?array
    {
        $html = $this->toUtf8($html);
        if (stripos($html, '<td') !== false || stripos($html, '<table') !== false) {
            $parsed = $this->parseTrackHtml($html, $code);
            if ($parsed) {
                return $parsed;
            }
        }

        return $this->parseTrackPlain($html, $code);
    }

    /**
     * El droplet no alcanza gc.cargotrack.net; r.jina.ai sí y devuelve el texto de track.asp.
     */
    protected function lookupViaReader(string $code): ?array
    {
        $trackUrl = rtrim((string) config('services.cargotrack.base_url'), '/').
            (string) config('services.cargotrack.track_path', '/m/track.asp').
            '?track='.rawurlencode($code).'&action2=process';
        $reader = 'https://r.jina.ai/'.$trackUrl;

        try {
            $response = Http::timeout(25)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0',
                    'Accept' => 'text/plain,text/markdown,text/html',
                ])
                ->get($reader);

            if (! $response->successful()) {
                $this->lastError = 'No se pudo leer CargoTrack (HTTP '.$response->status().').';

                return null;
            }

            $parsed = $this->parseScrapedHtml($response->body(), $code);
            if ($parsed) {
                $this->lastError = null;

                return $parsed;
            }

            return null;
        } catch (\Throwable $e) {
            $this->lastError = 'No se pudo consultar CargoTrack: '.$e->getMessage();
            Log::warning('CargoTrack reader scrape failed', [
                'code' => $code,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function parseTrackPlain(string $text, string $fallbackCode): ?array
    {
        $plain = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5);
        $plain = (string) preg_replace('/\*+/', ' ', $plain);
        $plain = (string) preg_replace('/\s+/', ' ', $plain);

        if (! preg_match('/Searching for/i', $plain)) {
            return null;
        }

        $statusRaw = null;
        if (preg_match(
            '/Searching for\s+\S+\s+(NOT FOUND|IN TRANSIT|IN DESTINATION COUNTRY|READY FOR PICKUP|READY FOR DELIVERY|RECEIVED|DELIVERED|WAREHOUSE|SHIPPED)/i',
            $plain,
            $m
        )) {
            $statusRaw = strtoupper(trim($m[1]));
        }

        if ($this->isNotFoundStatus($statusRaw)) {
            return null;
        }

        if ($statusRaw === null && ! preg_match('/piece\(s\)/i', $plain)) {
            return null;
        }

        $statusRaw = $statusRaw ?: 'IN TRANSIT';

        $pickupDate = null;
        if (preg_match(
            '/Received on\s*(\d{1,2}\/\d{1,2}\/\d{4})\s*at\s*(\d{1,2}:\d{2}:\d{2}\s*[AP]M)/i',
            $plain,
            $m
        )) {
            $pickupDate = $this->normalizeDate(trim($m[1]).' '.trim($m[2]));
        }

        $guia = null;
        if (preg_match('/[AP]M\s+(\d{5,9})\s+\d{10,}/i', $plain, $m)) {
            $guia = $m[1];
        } elseif (preg_match('/\b(\d{6,9})\b/', $plain, $m) && ! $this->looksLikeGuia($fallbackCode)) {
            $guia = $m[1];
        }

        $weight = null;
        if (preg_match('/([\d.]+)\s*lbs/i', $plain, $m)) {
            $weight = (float) $m[1];
        }

        $pieces = null;
        if (preg_match('/(\d+)\s*piece\(s\)/i', $plain, $m)) {
            $pieces = (int) $m[1];
        }

        $trackingNumber = $fallbackCode;
        if (preg_match('/\b([A-Z0-9]{12,})\b/i', $plain, $m) && ! $this->looksLikeGuia($m[1])) {
            $trackingNumber = $m[1];
        }

        $events = [];
        if (preg_match('/Tracking Events(.*?)(?:Login|Settings|$)/is', $plain, $block)) {
            if (preg_match_all(
                '/\b(Received|In Transit|Delivered|Shipped|Warehouse|In Destination Country)\b\s+(\d{1,2}\/\d{1,2}\/\d{4}\s+\d{1,2}:\d{2}(?::\d{2})?(?:\s*[AP]M)?)/i',
                $block[1],
                $rows,
                PREG_SET_ORDER
            )) {
                foreach ($rows as $row) {
                    $events[] = [
                        'status' => $row[1],
                        'description' => $row[1],
                        'date' => $row[2],
                        'evento_at' => $this->normalizeDate($row[2]),
                    ];
                }
            }
        }

        return [
            'tracking_number' => $trackingNumber,
            'status' => $this->mapStatus($statusRaw),
            'status_raw' => $statusRaw,
            'wrh' => $guia,
            'weight' => $weight,
            'description' => $pieces ? $pieces.' piece(s)' : null,
            'pickup_date' => $pickupDate,
            'delivery_date' => null,
            'events' => $events,
        ];
    }

    protected function scrapeTrack(string $url, string $base, string $code, int $timeout, bool $verify): ?string
    {
        $http = $this->http($timeout, verify: $verify)
            ->withHeaders([
                'Referer' => $url,
                'Origin' => $base,
            ]);

        // GET con query (como un scrape simple) y POST del form público.
        $get = $http->get($url, [
            'track' => $code,
            'action2' => 'process',
        ]);
        if ($get->successful() && stripos($get->body(), 'Searching for') !== false) {
            return $get->body();
        }

        $post = $http->asForm()->post($url, [
            'track' => $code,
            'action2' => 'process',
        ]);

        if (! $post->successful()) {
            $this->lastError = 'CargoTrack respondió HTTP '.$post->status();
            Log::warning('CargoTrack lookup HTTP error', [
                'code' => $code,
                'status' => $post->status(),
            ]);

            return null;
        }

        return $post->body();
    }

    protected function looksLikeSslError(string $message): bool
    {
        $key = strtolower($message);

        return str_contains($key, 'ssl')
            || str_contains($key, 'certificate')
            || str_contains($key, 'cainfo');
    }

    public function listPackagesForCredentials(string $username, string $password): array
    {
        $username = trim($username);
        $password = (string) $password;

        if ($username === '' || $password === '' || ! $this->enabled()) {
            return [];
        }

        $base = rtrim((string) config('services.cargotrack.base_url'), '/');
        $timeout = (int) config('services.cargotrack.timeout', 20);
        $cookieJar = [];

        try {
            if (! $this->login($base, $username, $password, $timeout, $cookieJar)) {
                Log::warning('CargoTrack login failed', ['user' => $username]);

                return [];
            }

            $codes = $this->discoverTrackingCodes($base, $timeout, $cookieJar);
            $packages = [];
            $seen = [];

            foreach ($codes as $code) {
                $key = strtoupper($code);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                $detail = $this->lookup($code);
                if ($detail) {
                    $packages[] = $detail;
                } else {
                    $packages[] = [
                        'tracking_number' => $code,
                        'status' => 'pendiente',
                        'status_raw' => null,
                        'wrh' => null,
                        'weight' => null,
                        'description' => null,
                        'pickup_date' => null,
                        'delivery_date' => null,
                        'events' => [],
                    ];
                }
            }

            return $packages;
        } catch (\Throwable $e) {
            Log::error('CargoTrack listPackages failed', [
                'user' => $username,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param  array<string, string>  $cookieJar
     */
    protected function login(string $base, string $username, string $password, int $timeout, array &$cookieJar): bool
    {
        $attempts = [
            ['url' => $base.'/default.asp', 'fields' => ['user' => $username, 'password' => $password, 'action' => 'login']],
            ['url' => $base.'/login.asp', 'fields' => ['user' => $username, 'password' => $password, 'action' => 'login', 'language' => '']],
            ['url' => $base.'/m/default.asp', 'fields' => ['username' => $username, 'password' => $password]],
        ];

        foreach ($attempts as $attempt) {
            $response = $this->http($timeout, $cookieJar)
                ->asForm()
                ->withHeaders(['Referer' => $attempt['url']])
                ->post($attempt['url'], $attempt['fields']);

            $this->mergeCookies($response->cookies(), $cookieJar);

            if (! $response->successful()) {
                continue;
            }

            $body = $response->body();
            if ($this->loginLooksFailed($body)) {
                continue;
            }

            // Confirmar con un GET autenticado: el home logueado no muestra el error ni el form público.
            $home = $this->http($timeout, $cookieJar)->get($base.'/default.asp');
            $this->mergeCookies($home->cookies(), $cookieJar);
            $homeBody = $home->body();

            if ($this->loginLooksFailed($homeBody)) {
                continue;
            }

            $loggedIn = stripos($homeBody, 'logout') !== false
                || stripos($homeBody, 'log out') !== false
                || (
                    stripos($homeBody, 'Forgot Password') === false
                    && stripos($homeBody, 'name="login"') === false
                );

            if ($loggedIn && $this->hasAspSession($cookieJar)) {
                return true;
            }
        }

        return false;
    }

    protected function loginLooksFailed(string $body): bool
    {
        return stripos($body, 'User and/or password are incorrect') !== false
            || stripos($body, 'password are incorrect') !== false
            || (stripos($body, 'ui-state-error') !== false && stripos($body, 'Alert:') !== false);
    }

    /**
     * @param  array<string, string>  $cookieJar
     * @return list<string>
     */
    protected function discoverTrackingCodes(string $base, int $timeout, array &$cookieJar): array
    {
        $paths = array_unique(array_filter(array_merge(
            (array) config('services.cargotrack.package_list_paths', []),
            [
                '/default.asp',
                '/m/default.asp',
                '/m/track.asp',
            ]
        )));

        $codes = [];
        $visited = [];

        foreach ($paths as $path) {
            $url = $base.'/'.ltrim((string) $path, '/');
            if (isset($visited[$url])) {
                continue;
            }
            $visited[$url] = true;

            try {
                $response = $this->http($timeout, $cookieJar)->get($url);
                $this->mergeCookies($response->cookies(), $cookieJar);
                if (! $response->successful()) {
                    continue;
                }

                $html = $response->body();
                if (stripos($html, '404.') !== false && stripos($html, 'Not Found') !== false) {
                    continue;
                }

                foreach ($this->extractAspLinks($html) as $link) {
                    $abs = $this->absolutize($base, $link);
                    if ($abs && ! isset($visited[$abs])) {
                        $paths[] = parse_url($abs, PHP_URL_PATH) ?: $link;
                    }
                }

                foreach ($this->extractTrackingCandidates($html) as $code) {
                    $codes[] = $code;
                }
            } catch (\Throwable $e) {
                Log::debug('CargoTrack discover page skip', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return array_values(array_unique($codes));
    }

    protected function parseTrackHtml(string $html, string $fallbackCode): ?array
    {
        if (! preg_match('/Searching for/i', $html)) {
            Log::info('CargoTrack HTML without tracking result', [
                'code' => $fallbackCode,
                'snippet' => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?: ''), 180),
            ]);

            return null;
        }

        $statusRaw = $this->extractStatus($html);
        $hasDetails = (bool) preg_match('/piece\(s\)/i', $html);

        if ($this->isNotFoundStatus($statusRaw) && ! $hasDetails) {
            return null;
        }

        if ($statusRaw === null && ! $hasDetails) {
            return null;
        }

        $statusRaw = $statusRaw ?: 'In Transit';

        $pickupDate = null;
        if (preg_match(
            '/Received on(?:&nbsp;|\s)*<strong>([^<]+)<\/strong>(?:&nbsp;|\s)*at(?:&nbsp;|\s)*<strong>([^<]+)<\/strong>/i',
            $html,
            $m
        )) {
            $pickupDate = $this->normalizeDate(trim($m[1]).' '.trim($m[2]));
        }

        // En GC: el número corto (6 dígitos) es WRH/guía; el código largo (ej. TBA…) es el tracking.
        $guia = null;
        $trackingNumber = null;
        $weight = null;
        $description = null;

        if (preg_match(
            '/<span class="ntextbig"><strong>([^<]+)<\/strong><\/span>\s*<br>\s*([^\s<][^\n<]*)?\s*<br>\s*<strong>(\d+)<\/strong>(?:&nbsp;|\s)*piece\(s\)(?:&nbsp;|\s)*with(?:&nbsp;|\s)*<strong>([\d.,]+)(?:&nbsp;|\s)*lbs<\/strong>/is',
            $html,
            $m
        )) {
            $primary = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
            $secondary = trim(html_entity_decode((string) ($m[2] ?? ''), ENT_QUOTES | ENT_HTML5));
            $secondary = preg_replace('/\s+/', '', $secondary) ?: '';
            if ($secondary !== '' && preg_match('/^(piece|with|lbs)/i', $secondary)) {
                $secondary = '';
            }

            foreach ([$primary, $secondary] as $token) {
                if ($token === '') {
                    continue;
                }
                if ($this->looksLikeGuia($token)) {
                    $guia = $guia ?: $token;
                } else {
                    $trackingNumber = $trackingNumber ?: $token;
                }
            }

            $weight = (float) str_replace(',', '', $m[4]);
            $pieces = (int) $m[3];
            // Solo descripción operativa (piezas); la guía va en su propio campo.
            $description = $pieces > 0 ? $pieces.' piece(s)' : null;
        }

        // Si buscan por guía de 6 dígitos y no vino tracking largo, conservar el código buscado como guía.
        if ($guia === null && $this->looksLikeGuia($fallbackCode)) {
            $guia = $fallbackCode;
        }
        if ($trackingNumber === null) {
            // Tracking = código largo si existe; si no, el buscado solo si NO es guía pura.
            $trackingNumber = $this->looksLikeGuia($fallbackCode)
                ? ($guia ?: $fallbackCode)
                : $fallbackCode;
        }

        $events = [];
        if (preg_match_all(
            '/<td class="ntextrow">\s*([^<]+?)\s*<br>\s*<span class="ntext">\s*([^<]+?)\s*<\/span>/is',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $row) {
                $label = html_entity_decode(trim($row[1]), ENT_QUOTES | ENT_HTML5);
                $when = html_entity_decode(trim(str_replace('&nbsp;', ' ', $row[2])), ENT_QUOTES | ENT_HTML5);
                if ($label === '' || stripos($label, 'Tracking Events') !== false) {
                    continue;
                }
                $events[] = [
                    'status' => $label,
                    'description' => $label,
                    'date' => $when,
                    'evento_at' => $this->normalizeDate($when),
                ];
            }
        }

        // Cronológico: más antiguo → más reciente (para línea de tiempo).
        $events = array_reverse($events);

        return [
            'tracking_number' => $trackingNumber,
            'status' => $this->mapStatus($statusRaw),
            'status_raw' => $statusRaw,
            'wrh' => $guia,
            'weight' => $weight,
            'description' => $description,
            'pickup_date' => $pickupDate,
            'delivery_date' => null,
            'events' => $events,
        ];
    }

    protected function extractStatus(string $html): ?string
    {
        if (! preg_match('/Searching for.*?<\/tr>\s*<tr>\s*<td[^>]*>(.*?)<\/td>/is', $html, $m)) {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5));
        $text = preg_replace('/\s+/', ' ', $text) ?: $text;

        return $text !== '' ? $text : null;
    }

    protected function isNotFoundStatus(?string $status): bool
    {
        if ($status === null || trim($status) === '') {
            return false;
        }

        $key = strtoupper(trim($status));

        return $key === 'NOT FOUND' || $key === 'NOTFOUND';
    }

    protected function looksLikeGuia(string $value): bool
    {
        return (bool) preg_match('/^\d{5,9}$/', trim($value));
    }

    public function mapStatus(string $raw): string
    {
        $key = Str::lower(trim($raw));
        $key = strtr($key, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        ]);
        $key = preg_replace('/\s+/', ' ', $key) ?: $key;

        $map = config('services.cargotrack.estado_map', []);
        if (isset($map[$key])) {
            return (string) $map[$key];
        }

        $compact = str_replace(' ', '_', $key);
        if (isset($map[$compact])) {
            return (string) $map[$compact];
        }

        return match (true) {
            str_contains($key, 'deliver') => 'entregado',
            str_contains($key, 'destination') => 'en_aduana',
            str_contains($key, 'transit') || str_contains($key, 'shipped') => 'en_transito',
            str_contains($key, 'receiv') || str_contains($key, 'warehouse') || str_contains($key, 'bodega') => 'recibido',
            // "Listo" solo lo marcamos nosotros al ingresar el paquete en el CRM.
            str_contains($key, 'ready') => 'en_aduana',
            str_contains($key, 'out for delivery') => 'en_aduana',
            str_contains($key, 'return') => 'devuelto',
            str_contains($key, 'cancel') => 'cancelado',
            default => 'en_transito',
        };
    }

    protected function toUtf8(string $html): string
    {
        if (mb_check_encoding($html, 'UTF-8')) {
            // Puede ser UTF-8 válido por casualidad aun siendo latin1; si hay charset iso, convertir.
            if (preg_match('/charset\s*=\s*["\']?iso-8859-1/i', $html)) {
                $converted = @mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');

                return is_string($converted) ? $converted : $html;
            }

            return $html;
        }

        $converted = @mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');

        return is_string($converted) ? $converted : utf8_encode($html);
    }

    protected function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse(str_replace("\xc2\xa0", ' ', $value))->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return list<string>
     */
    protected function extractTrackingCandidates(string $html): array
    {
        $codes = [];
        if (preg_match_all('/\b([A-Z]{0,3}\d{5,14})\b/', strip_tags($html), $m)) {
            foreach ($m[1] as $code) {
                // Skip years / common noise
                if (preg_match('/^(19|20)\d{2}$/', $code)) {
                    continue;
                }
                if (strlen($code) < 5) {
                    continue;
                }
                $codes[] = $code;
            }
        }

        return $codes;
    }

    /**
     * @return list<string>
     */
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

    protected function absolutize(string $base, string $href): ?string
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

    /**
     * @param  array<string, string>  $cookieJar
     */
    protected function http(int $timeout, array $cookieJar = [], ?CookieJar $jar = null, bool $verify = true): PendingRequest
    {
        $options = [
            'allow_redirects' => true,
            'force_ip_resolve' => 'v4',
            'connect_timeout' => 5,
            'verify' => $verify,
        ];
        if ($jar !== null) {
            $options['cookies'] = $jar;
        }

        $request = Http::timeout($timeout)
            ->withHeaders([
                'User-Agent' => (string) config(
                    'services.cargotrack.user_agent',
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ),
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9,es;q=0.8',
            ])
            ->withOptions($options);

        if ($cookieJar !== []) {
            $parts = [];
            foreach ($cookieJar as $name => $value) {
                $parts[] = $name.'='.$value;
            }
            $request = $request->withHeaders(['Cookie' => implode('; ', $parts)]);
        }

        return $request;
    }

    /**
     * @param  iterable<int, \GuzzleHttp\Cookie\SetCookie>|mixed  $cookies
     * @param  array<string, string>  $cookieJar
     */
    protected function mergeCookies(mixed $cookies, array &$cookieJar): void
    {
        if (! is_iterable($cookies)) {
            return;
        }

        foreach ($cookies as $cookie) {
            $name = method_exists($cookie, 'getName') ? $cookie->getName() : null;
            $value = method_exists($cookie, 'getValue') ? $cookie->getValue() : null;
            if ($name && $value !== null) {
                $cookieJar[$name] = $value;
            }
        }
    }

    /**
     * @param  array<string, string>  $cookieJar
     */
    protected function hasAspSession(array $cookieJar): bool
    {
        foreach (array_keys($cookieJar) as $name) {
            if (str_starts_with(strtoupper((string) $name), 'ASPSESSIONID')) {
                return true;
            }
        }

        return false;
    }
}
