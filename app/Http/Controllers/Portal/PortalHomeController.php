<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Facturacion;
use App\Models\Inventario;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;

class PortalHomeController extends Controller
{
    public function index()
    {
        $clienteId = Auth::user()->cliente_id;

        $paquetesQuery = Inventario::where('cliente_id', $clienteId);
        $totalPaquetes = (clone $paquetesQuery)->count();
        $recibidos = (clone $paquetesQuery)->where('estado', 'recibido')->count();
        $entregados = (clone $paquetesQuery)->where('estado', 'entregado')->count();
        $otros = max(0, $totalPaquetes - $recibidos - $entregados);

        $facturasPendientes = Facturacion::where('cliente_id', $clienteId)
            ->noAnulada()
            ->whereIn('estado_pago', ['pendiente', 'parcial', 'entregado_sin_pagar'])
            ->count();

        $ultimosPaquetes = Inventario::with('servicio')
            ->where('cliente_id', $clienteId)
            ->latest('fecha_ingreso')
            ->take(5)
            ->get();

        $ultimasFacturas = Facturacion::where('cliente_id', $clienteId)
            ->noAnulada()
            ->latest('fecha_factura')
            ->take(5)
            ->get();

        $notificaciones = Notificacion::where('user_id', Auth::id())
            ->orderByDesc('fecha')
            ->take(8)
            ->get();

        return view('portal.home', compact(
            'totalPaquetes',
            'recibidos',
            'entregados',
            'otros',
            'facturasPendientes',
            'ultimosPaquetes',
            'ultimasFacturas',
            'notificaciones'
        ));
    }
}
