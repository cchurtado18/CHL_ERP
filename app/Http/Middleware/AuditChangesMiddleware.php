<?php

namespace App\Http\Middleware;

use App\Models\Log;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditChangesMiddleware
{
    /** Rutas que no se auditan (consulta, auth o la propia bitácora). */
    private array $skipPrefixes = [
        'login',
        'logout',
        'sin-acceso',
        'auditoria',
        'logs-inventario',
        'rastreo',
        'up',
        'livewire',
    ];

    /** @var string[] */
    private array $skipPathNeedles = [
        'preview',
        'validar-numero',
        'estadisticas',
        'vencidos/count',
        'notif-unread',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->registrarCambioSiAplica($request, $response);
        } catch (Throwable $e) {
            // No bloquear el flujo principal por fallas de bitácora.
        }

        return $response;
    }

    private function registrarCambioSiAplica(Request $request, Response $response): void
    {
        $user = $request->user();
        if (! $user) {
            return;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        if ($response->getStatusCode() >= 400) {
            return;
        }

        $firstSegment = strtolower((string) $request->segment(1));
        if ($firstSegment === '' || in_array($firstSegment, $this->skipPrefixes, true)) {
            return;
        }

        $path = strtolower($request->path());
        foreach ($this->skipPathNeedles as $needle) {
            if (str_contains($path, $needle)) {
                return;
            }
        }

        $accion = match ($request->method()) {
            'POST' => 'crear',
            'PUT', 'PATCH' => 'editar',
            'DELETE' => 'eliminar',
            default => 'accion',
        };

        $routeName = (string) ($request->route()?->getName() ?? 'sin_ruta');
        $routeParams = [];
        foreach (($request->route()?->parameters() ?? []) as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $routeParams[$k] = $v;
            } elseif (is_object($v) && method_exists($v, 'getKey')) {
                $routeParams[$k] = $v->getKey();
            } else {
                $routeParams[$k] = (string) $v;
            }
        }

        $payload = $this->sanitizePayload($request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'current_password',
            'remember',
        ]));

        $descripcion = json_encode([
            'rol' => $user->rol,
            'ruta' => $routeName,
            'metodo' => $request->method(),
            'url' => $request->path(),
            'params' => $routeParams,
            'payload' => $payload,
            'ip' => $request->ip(),
        ], JSON_UNESCAPED_UNICODE);

        Log::create([
            'user_id' => $user->id,
            'modulo' => $firstSegment,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'fecha' => now(),
        ]);
    }

    /** @param  array<string, mixed>  $payload */
    private function sanitizePayload(array $payload): array
    {
        $clean = [];
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $this->sanitizePayload($value);
                continue;
            }
            if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
                $clean[$key] = $value;
                continue;
            }
            $text = (string) $value;
            $clean[$key] = mb_strlen($text) > 180 ? mb_substr($text, 0, 177).'...' : $text;
        }

        return $clean;
    }
}
