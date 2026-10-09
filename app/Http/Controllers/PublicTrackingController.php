<?php

namespace App\Http\Controllers;

use App\Contracts\TrackingProvider;
use App\Models\Inventario;
use Illuminate\Http\Request;

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
            $paquete = Inventario::with(['servicio', 'estadoEventos'])
                ->where(function ($q) use ($code) {
                    $q->where('tracking_codigo', $code)
                        ->orWhere('numero_guia', $code);
                })
                ->first();

            if ($paquete) {
                $fuente = 'local';
            } elseif ($provider->enabled()) {
                $remoto = $provider->lookup($code);
                if ($remoto) {
                    $fuente = $provider->driver();
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
