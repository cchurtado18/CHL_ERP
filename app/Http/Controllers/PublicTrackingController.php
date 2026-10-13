<?php

namespace App\Http\Controllers;

use App\Contracts\TrackingProvider;
use Illuminate\Http\Request;

/**
 * Rastreo público: toma el código escrito y lo consulta en CargoTrack.
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

        $remoto = null;
        $fuente = null;
        $error = null;

        if ($code !== '' && $provider->enabled()) {
            $remoto = $provider->lookup($code);
            if ($remoto) {
                $fuente = $provider->driver();
            } else {
                $error = $provider->lastError();
            }
        } elseif ($code !== '' && ! $provider->enabled()) {
            $error = 'El rastreo de CargoTrack no está activo en este servidor.';
        }

        return view('public.tracking', [
            'code' => $code,
            'paquete' => null,
            'remoto' => $remoto,
            'fuente' => $fuente,
            'buscado' => $code !== '',
            'error' => $error,
        ]);
    }
}
