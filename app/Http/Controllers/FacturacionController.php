<?php

namespace App\Http\Controllers;

use App\Models\Facturacion;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\FacturaMailable;

class FacturacionController extends Controller
{
    // Listar facturas
    public function index(Request $request)
    {
        $facturas = Facturacion::with('cliente')
            ->when($request->filled('cliente'), function($q) use ($request) {
                $q->whereHas('cliente', function($q2) use ($request) {
                    $q2->where('nombre_completo', 'like', '%'.$request->cliente.'%');
                });
            })
            ->when($request->filled('fecha'), function($q) use ($request) {
                $q->where('fecha_factura', $request->fecha);
            })
            ->when($request->filled('acta'), function($q) use ($request) {
                $q->where('numero_acta', 'like', '%'.$request->acta.'%');
            })
            ->when($request->filled('estado'), function($q) use ($request) {
                $q->where('estado_pago', $request->estado);
            })
            ->latest()
            ->paginate(10);
        return view('facturacion.index', compact('facturas'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        $clientes = \App\Models\Cliente::select('id', 'nombre_completo')->orderBy('nombre_completo')->get();
        return view('facturacion.create', compact('clientes'));
    }

    // Guardar nueva factura
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'fecha_factura'  => 'required|date',
            'numero_acta'    => 'required|string|max:100',
            'moneda'         => 'required|in:USD,NIO',
            'tasa_cambio'    => 'nullable|numeric',
            'monto_local'    => 'required|numeric',
            'estado_pago'    => 'required|in:pendiente,parcial,pagado,entregado_pagado,entregado_sin_pagar,pagado_sin_entregar,facturado_npne',
            'nota'           => 'nullable|string',
            'paquetes'       => 'required|array|min:1',
            'paquetes.*'     => 'exists:inventario,id',
            'delivery'       => 'nullable|numeric',
        ]);

        // Validar que los paquetes pertenezcan al cliente, no estén facturados y no estén entregados
        $paquetes = \App\Models\Inventario::whereIn('id', $request->paquetes)
            ->where('cliente_id', $request->cliente_id)
            ->whereNull('factura_id')
            ->where('estado', '!=', 'entregado') // Excluir paquetes ya entregados
            ->get();
        if (count($paquetes) !== count($request->paquetes)) {
            return back()->withErrors(['paquetes' => 'Uno o más paquetes seleccionados no pertenecen al cliente, ya han sido facturados o ya están entregados.'])->withInput();
        }

        // Calcular monto total real
        $monto_total = 0;
        foreach ($paquetes as $p) {
            $peso = floatval($p->peso_lb ?? 0);
            $tarifa = floatval($p->tarifa_manual ?? $p->tarifa ?? 1);
            $monto_total += $peso * $tarifa;
        }
        $delivery = floatval($request->delivery ?? 0);
        $monto_total += $delivery;

        $factura = Facturacion::create([
            'cliente_id'    => $request->cliente_id,
            'fecha_factura' => $request->fecha_factura,
            'numero_acta'   => $request->numero_acta,
            'monto_total'   => $monto_total,
            'moneda'        => $request->moneda,
            'tasa_cambio'   => $request->tasa_cambio,
            'monto_local'   => $request->monto_local,
            'estado_pago'   => $request->estado_pago,
            'nota'          => $request->nota,
            'delivery'      => $delivery,
            'created_by'    => \Auth::id(),
        ]);

        // Asociar paquetes seleccionados a la factura
        \App\Models\Inventario::whereIn('id', $paquetes->pluck('id'))->update(['factura_id' => $factura->id]);
        // Si el estado de pago es entregado_pagado o entregado_sin_pagar, cambiar estado de los paquetes a 'entregado'
        if (in_array($request->estado_pago, ['entregado_pagado', 'entregado_sin_pagar'])) {
            \App\Models\Inventario::whereIn('id', $paquetes->pluck('id'))->update(['estado' => 'entregado']);
        }

        return redirect()->route('facturacion.index')->with('success', 'Factura registrada correctamente.');
    }

    // Editar factura
    public function edit($id)
    {
        $factura = Facturacion::findOrFail($id);
        $clientes = Cliente::all();
        return view('facturacion.edit', compact('factura', 'clientes'));
    }

    // Actualizar factura
    public function update(Request $request, $id)
    {
        $factura = Facturacion::findOrFail($id);

        $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'fecha_factura'  => 'required|date',
            'numero_acta'    => 'nullable|string|max:100',
            'monto_total'    => 'required|numeric',
            'moneda'         => 'required|in:USD,NIO',
            'tasa_cambio'    => 'nullable|numeric',
            'monto_local'    => 'required|numeric',
            'estado_pago'    => 'required|in:pendiente,parcial,pagado,entregado_pagado,entregado_sin_pagar,pagado_sin_entregar,facturado_npne',
            'nota'           => 'nullable|string',
            'delivery'       => 'nullable|numeric',
        ]);

        $factura->update([
            'cliente_id'    => $request->cliente_id,
            'fecha_factura' => $request->fecha_factura,
            'numero_acta'   => $request->numero_acta,
            'monto_total'   => $request->monto_total,
            'moneda'        => $request->moneda,
            'tasa_cambio'   => $request->tasa_cambio,
            'monto_local'   => $request->monto_local,
            'estado_pago'   => $request->estado_pago,
            'nota'          => $request->nota,
            'delivery'      => $request->delivery,
            'updated_by'    => Auth::id(),
        ]);

        return redirect()->route('facturacion.index')->with('success', 'Factura actualizada.');
    }

    // Eliminar factura
    public function destroy($id)
    {
        $factura = Facturacion::findOrFail($id);
        $factura->delete();

        return redirect()->route('facturacion.index')->with('success', 'Factura eliminada.');
    }

    public function descargarPDF($id)
    {
        $factura = \App\Models\Facturacion::with(['cliente', 'paquetes.servicio'])->findOrFail($id);
        // Puedes agregar más relaciones si necesitas más datos
        $pdf = Pdf::loadView('facturacion.pdf', compact('factura'));
        return $pdf->download('factura_'.$factura->id.'.pdf');
    }

    public function previsualizarPDF($id)
    {
        $factura = \App\Models\Facturacion::with(['cliente', 'paquetes.servicio'])->findOrFail($id);
        $pdf = Pdf::loadView('facturacion.pdf', compact('factura'));
        return $pdf->stream('factura_'.$factura->id.'.pdf');
    }

    public function previewLivePDF(Request $request)
    {
        // Crear un objeto temporal con los datos recibidos
        $factura = new \App\Models\Facturacion($request->all());
        // Simular relación con cliente
        $factura->cliente = (object) [
            'nombre_completo' => $request->input('cliente_nombre', ''),
            'direccion' => $request->input('cliente_direccion', ''),
            'telefono' => $request->input('cliente_telefono', ''),
        ];
        $paquetes = [];
        
        // Solo agregar paquetes seleccionados explícitamente
        if ($request->has('paquetes') && !empty($request->input('paquetes'))) {
            foreach ($request->input('paquetes', []) as $i => $id) {
                $peso = floatval($request->input('paquete_peso_'.$id, 0));
                $tarifa = floatval($request->input('paquete_tarifa_'.$id, 0));
                $monto = floatval($request->input('paquete_valor_'.$id, 0));
                if ($monto == 0 && $peso > 0 && $tarifa > 0) {
                    $monto = $peso * $tarifa;
                }
                $paquetes[] = [
                    'numero_guia' => $request->input('paquete_guia_'.$id, ''),
                    'notas' => $request->input('paquete_descripcion_'.$id, ''),
                    'tracking_codigo' => $request->input('paquete_tracking_'.$id, ''),
                    'servicio' => $request->input('paquete_servicio_'.$id, ''),
                    'peso_lb' => $peso,
                    'tarifa_manual' => null,
                    'tarifa' => $tarifa,
                    'monto_calculado' => $monto,
                ];
            }
        }
        
        $factura->paquetes = collect($paquetes);
        $factura->delivery = $request->input('delivery', 0);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('facturacion.pdf', compact('factura'));
        return $pdf->stream('preview.pdf');
    }

    /**
     * Retorna los productos del inventario de un cliente para facturación (AJAX)
     */
    public function paquetesPorCliente($clienteId)
    {
        $inventarios = \App\Models\Inventario::where('cliente_id', $clienteId)
            ->whereNull('factura_id')
            ->where('estado', '!=', 'entregado') // Excluir paquetes ya entregados
            ->with('servicio')
            ->get()
            ->map(function($inv) {
                return [
                    'id' => $inv->id,
                    'numero_guia' => $inv->numero_guia,
                    'notas' => $inv->notas,
                    'tracking_codigo' => $inv->tracking_codigo,
                    'servicio' => $inv->servicio ? $inv->servicio->tipo_servicio : null,
                    'tarifa_manual' => $inv->tarifa_manual,
                    'monto_calculado' => $inv->monto_calculado,
                    'peso_lb' => $inv->peso_lb,
                    'estado' => $inv->estado, // Agregar el estado del paquete
                ];
            });
        return response()->json($inventarios);
    }

    /**
     * API: Retorna datos del cliente, historial de facturas y paquetes no facturados
     */
    public function clienteDetalle($clienteId)
    {
        $cliente = \App\Models\Cliente::select('id', 'nombre_completo', 'telefono', 'direccion')->findOrFail($clienteId);
        $paquetes = \App\Models\Inventario::where('cliente_id', $clienteId)
            ->whereNull('factura_id')
            ->where('estado', '!=', 'entregado') // Excluir paquetes ya entregados
            ->with('servicio')
            ->get()
            ->map(function($inv) {
                return [
                    'id' => $inv->id,
                    'numero_guia' => $inv->numero_guia,
                    'notas' => $inv->notas,
                    'tracking_codigo' => $inv->tracking_codigo,
                    'servicio' => $inv->servicio ? $inv->servicio->tipo_servicio : null,
                    'tarifa_manual' => $inv->tarifa_manual,
                    'monto_calculado' => $inv->monto_calculado,
                    'peso_lb' => $inv->peso_lb,
                    'estado' => $inv->estado, // Agregar el estado del paquete
                ];
            });
        $historial = \App\Models\Facturacion::where('cliente_id', $clienteId)
            ->orderByDesc('fecha_factura')
            ->take(5)
            ->get(['id', 'fecha_factura', 'monto_total', 'estado_pago']);
        return response()->json([
            'success' => true,
            'cliente' => $cliente,
            'paquetes' => $paquetes,
            'historial' => $historial,
        ]);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $factura = Facturacion::findOrFail($id);
        $request->validate([
            'estado_pago' => 'required|in:pendiente,parcial,pagado,entregado_pagado,entregado_sin_pagar,pagado_sin_entregar,facturado_npne',
        ]);
        $factura->estado_pago = $request->estado_pago;
        $factura->save();

        // Si el nuevo estado es entregado_pagado o entregado_sin_pagar, actualizar productos relacionados
        if (in_array($request->estado_pago, ['entregado_pagado', 'entregado_sin_pagar'])) {
            \App\Models\Inventario::where('factura_id', $factura->id)->update(['estado' => 'entregado']);
        }

        return redirect()->route('facturacion.index')->with('success', 'Estado de la factura actualizado.');
    }

    public function enviarCorreo($id)
    {
        $factura = \App\Models\Facturacion::with('cliente')->findOrFail($id);
        $correo = $factura->cliente->correo ?? null;
        if (!$correo) {
            return back()->with('error', 'El cliente no tiene correo.');
        }
        // Generar PDF temporal
        $pdf = \PDF::loadView('facturacion.pdf', ['factura' => $factura]);
        $pdfContent = $pdf->output();
        // Enviar correo
        Mail::to($correo)->send(new FacturaMailable($factura, $pdfContent));
        return back()->with('success', 'Factura enviada correctamente a ' . $correo);
    }
}
