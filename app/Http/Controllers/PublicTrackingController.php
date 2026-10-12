<?php

namespace App\Http\Controllers;

use App\Contracts\TrackingProvider;
use App\Models\Inventario;
use App\Support\TrackingCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Rastreo público sin login (por guía o tracking).
 * Primero busca en inventario local; si no hay, consulta el proveedor externo.
 */
class PublicTrackingController extends Controller
{
    public function index(Request $request, TrackingProvider $provider)
    {
        $code = trim((string) (
            $request->query('tracking', '')
            ?: $request->query('code', '')
            ?: $request->input('tracking', '')
            ?: $request->input('code', '')
        ));

        $paquete = null;
        $remoto = null;
        $fuente = null;

        if ($code !== '') {
            $paquete = Inventario::findByPublicCode($code);
            if ($paquete) {
                $paquete->load(['servicio', 'estadoEventos']);
                $fuente = 'local';
            } elseif ($provider->enabled()) {
                $remoto = $provider->lookup($code);
                if ($remoto) {
                    $fuente = $provider->driver();
                } else {
                    Log::info('Rastreo público sin coincidencia', [
                        'code' => $code,
                        'normalized' => TrackingCode::normalize($code),
                        'provider' => $provider->driver(),
                    ]);
                }
            }
        }

        return view('public.tracking', [
            'code' => $code,
            'paquete' => $paquete,
            'remoto' => $remoto,
            'fuente' => $fuente,
            'buscado' => $code !== '',
        ]);
    }
}
