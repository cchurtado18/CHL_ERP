<?php

namespace App\Http\Middleware;

use App\Models\Log;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditChangesMiddleware
{
    /** @var string[] */
    private array $trackedPrefixes = [
        'leads',
        'facturacion',
        'inventario',
        'tracking',
        'notificaciones',
        'contabilidad',
        'encomiendas',
        'remitentes',
        'destinatarios',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->registrarCambioSiAplica($request, $response);
        } catch (Throwable $e) {
            // No bloquear flujo principal por fallas de bitácora.
        }

        return $response;
    }

    private function registrarCambioSiAplica(Request $request, Response $response): void
    {
        $user = $request->user();
        if (! $user) {
            return;
        }

        if (! in_array((string) $user->rol, ['agente', 'auditor', 'basico'], true)) {
            return;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        if ($response->getStatusCode() >= 400) {
            return;
        }

        $firstSegment = (string) $request->segment(1);
        if (! in_array($firstSegment, $this->trackedPrefixes, true)) {
            return;
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

        $payload = $request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'current_password',
        ]);

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
}

