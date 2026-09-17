<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Facturacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalFacturaController extends Controller
{
    public function index(Request $request)
    {
        $clienteId = Auth::user()->cliente_id;

        $query = Facturacion::where('cliente_id', $clienteId)->noAnulada();

        if ($request->filled('estado_pago')) {
            $query->where('estado_pago', $request->estado_pago);
        }

        $facturas = $query->latest('fecha_factura')->paginate(15)->withQueryString();

        return view('portal.facturas.index', compact('facturas'));
    }

    public function show($id)
    {
        $factura = Facturacion::with(['paquetes.servicio', 'pagos'])
            ->where('cliente_id', Auth::user()->cliente_id)
            ->noAnulada()
            ->findOrFail($id);

        return view('portal.facturas.show', compact('factura'));
    }

    public function pdf($id)
    {
        $factura = Facturacion::with(['cliente', 'paquetes.servicio', 'encomienda', 'pagos'])
            ->where('cliente_id', Auth::user()->cliente_id)
            ->noAnulada()
            ->findOrFail($id);

        $view = ($factura->tipo_factura ?? 'paqueteria') === 'encomienda_familiar'
            ? 'facturacion.pdf-encomienda'
            : 'facturacion.pdf-paqueteria';

        $pdf = Pdf::loadView($view, compact('factura'));

        return $pdf->download('factura_folio_'.$factura->etiquetaFolio().'.pdf');
    }
}
