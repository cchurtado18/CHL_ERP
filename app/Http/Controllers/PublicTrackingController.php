<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;

/**
 * Rastreo público sin login (por guía o tracking).
 */
class PublicTrackingController extends Controller
{
    public function index(Request $request)
    {
        $code = trim((string) $request->query('code', ''));
        $paquete = null;

        if ($code !== '') {
            $paquete = Inventario::with(['servicio', 'estadoEventos'])
                ->where(function ($q) use ($code) {
                    $q->where('tracking_codigo', $code)
                        ->orWhere('numero_guia', $code);
                })
                ->first();
        }

        return view('public.tracking', [
            'code' => $code,
            'paquete' => $paquete,
            'buscado' => $code !== '',
        ]);
    }
}
