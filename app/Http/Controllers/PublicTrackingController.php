<?php

namespace App\Http\Controllers;

use App\Contracts\TrackingProvider;
use App\Services\Tracking\CargoTrackProvider;
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

        $cargotrackUrl = rtrim((string) config('services.cargotrack.base_url'), '/').
            (string) config('services.cargotrack.track_path', '/m/track.asp');
        $errorText = strtolower((string) $error);
        $usarNavegador = $error !== null
            && $remoto === null
            && ! str_contains($errorText, 'no está configurado')
            && ! str_contains($errorText, 'no está activo');

        return view('public.tracking', [
            'code' => $code,
            'paquete' => null,
            'remoto' => $remoto,
            'fuente' => $fuente,
            'buscado' => $code !== '',
            'error' => $error,
            'cargotrackUrl' => $cargotrackUrl,
            'usarNavegador' => $usarNavegador,
        ]);
    }

    public function parse(Request $request, CargoTrackProvider $provider)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:80'],
            'html' => ['required', 'string', 'max:80000'],
        ]);

        $remoto = $provider->parseScrapedHtml($data['html'], $data['code']);

        if (! $remoto) {
            return response()->json([
                'ok' => false,
                'message' => 'CargoTrack no tiene ese código.',
            ]);
        }

        return response()->json([
            'ok' => true,
            'remoto' => $remoto,
        ]);
    }
}
