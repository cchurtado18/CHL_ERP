<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalPaqueteController extends Controller
{
    public function index(Request $request)
    {
        $clienteId = Auth::user()->cliente_id;

        $base = Inventario::where('cliente_id', $clienteId);
        $totalPaquetes = (clone $base)->count();
        $totalRecibidos = (clone $base)->where('estado', 'recibido')->count();
        $totalEntregados = (clone $base)->where('estado', 'entregado')->count();

        $query = Inventario::with('servicio')
            ->where('cliente_id', $clienteId);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->where('tracking_codigo', 'like', "%{$q}%")
                    ->orWhere('numero_guia', 'like', "%{$q}%")
                    ->orWhere('notas', 'like', "%{$q}%");
            });
        }

        $paquetes = $query->latest('fecha_ingreso')->paginate(15)->withQueryString();

        return view('portal.paquetes.index', compact(
            'paquetes',
            'totalPaquetes',
            'totalRecibidos',
            'totalEntregados'
        ));
    }

    public function show($id)
    {
        $paquete = Inventario::with(['servicio', 'factura', 'estadoEventos'])
            ->where('cliente_id', Auth::user()->cliente_id)
            ->findOrFail($id);

        return view('portal.paquetes.show', compact('paquete'));
    }
}
