<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Notificacion;
use App\Models\PaqueteEstadoEvento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrimetrackClient
{
    public function configured(): bool
    {
        $base = rtrim((string) config('services.primetrack.base_url'), '/');
        $key = (string) config('services.primetrack.api_key');

        return $base !== '' && $key !== '';
    }

    /**
     * Consulta estado en Primetrack por código de tracking o guía.
     *
     * Espera JSON tipo:
     * {
     *   "estado": "en_transito",
     *   "ubicacion": "...",
     *   "descripcion": "...",
     *   "evento_at": "2026-09-16T12:00:00Z",
     *   "eventos": [{ "estado", "ubicacion", "descripcion", "evento_at" }]
     * }
     */
    public function consultarEstado(string $codigo): ?array
    {
        if (! $this->configured()) {
            return null;
        }

        $base = rtrim((string) config('services.primetrack.base_url'), '/');
        $endpoint = (string) config('services.primetrack.status_path', '/api/tracking/{code}');
        $url = $base.str_replace('{code}', urlencode($codigo), $endpoint);
        $timeout = (int) config('services.primetrack.timeout', 15);

        try {
            $response = Http::timeout($timeout)
                ->withToken((string) config('services.primetrack.api_key'))
                ->acceptJson()
                ->get($url);

            if (! $response->successful()) {
                Log::warning('Primetrack consulta fallida', [
                    'codigo' => $codigo,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Primetrack error de conexión', [
                'codigo' => $codigo,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sincroniza un paquete del inventario con Primetrack.
     * Retorna true si hubo cambios.
     */
    public function syncPaquete(Inventario $paquete): bool
    {
        $codigo = $paquete->tracking_codigo ?: $paquete->numero_guia;
        if (! $codigo) {
            return false;
        }

        $data = $this->consultarEstado((string) $codigo);
        if (! is_array($data) || empty($data)) {
            return false;
        }

        $estadoRemoto = $this->mapEstado((string) ($data['estado'] ?? ''));
        if ($estadoRemoto === '') {
            return false;
        }

        $changed = false;
        $estadoAnterior = $paquete->estado;

        if ($paquete->estado !== $estadoRemoto) {
            $paquete->estado = $estadoRemoto;
            $paquete->save();
            $changed = true;
        }

        $eventos = $data['eventos'] ?? null;
        if (! is_array($eventos) || $eventos === []) {
            $eventos = [[
                'estado' => $estadoRemoto,
                'ubicacion' => $data['ubicacion'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'evento_at' => $data['evento_at'] ?? now()->toIso8601String(),
            ]];
        }

        foreach ($eventos as $evento) {
            $estadoEvt = $this->mapEstado((string) ($evento['estado'] ?? $estadoRemoto));
            if ($estadoEvt === '') {
                continue;
            }

            $eventoAt = isset($evento['evento_at'])
                ? Carbon::parse($evento['evento_at'])
                : now();

            $exists = PaqueteEstadoEvento::where('inventario_id', $paquete->id)
                ->where('estado', $estadoEvt)
                ->where('estado_origen', 'primetrack')
                ->where('evento_at', $eventoAt)
                ->exists();

            if ($exists) {
                continue;
            }

            PaqueteEstadoEvento::create([
                'inventario_id' => $paquete->id,
                'estado' => $estadoEvt,
                'estado_origen' => 'primetrack',
                'ubicacion' => $evento['ubicacion'] ?? ($data['ubicacion'] ?? null),
                'descripcion' => $evento['descripcion'] ?? ($data['descripcion'] ?? null),
                'evento_at' => $eventoAt,
                'payload' => $evento,
            ]);
            $changed = true;
        }

        if ($changed && $estadoAnterior !== $estadoRemoto) {
            $this->notificarCambioEstado($paquete, $estadoAnterior, $estadoRemoto);
        }

        return $changed;
    }

    public function mapEstado(string $estado): string
    {
        $estado = strtolower(trim($estado));
        $estado = strtr($estado, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        ]);

        $map = config('services.primetrack.estado_map', []);
        if (isset($map[$estado])) {
            return (string) $map[$estado];
        }

        // Fallback: normaliza espacios a guion bajo
        return preg_replace('/[^a-z0-9_]+/', '_', str_replace(' ', '_', $estado)) ?: '';
    }

    protected function notificarCambioEstado(Inventario $paquete, string $antes, string $despues): void
    {
        $user = User::where('rol', 'cliente')
            ->where('cliente_id', $paquete->cliente_id)
            ->where('estado', true)
            ->first();

        if (! $user) {
            return;
        }

        Notificacion::create([
            'user_id' => $user->id,
            'titulo' => 'Actualización de paquete',
            'mensaje' => sprintf(
                'Tu paquete %s cambió de estado: %s → %s.',
                $paquete->tracking_codigo ?: $paquete->numero_guia ?: ('#'.$paquete->id),
                $antes,
                $despues
            ),
            'leido' => false,
            'fecha' => now(),
        ]);
    }
}
