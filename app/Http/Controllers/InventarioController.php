<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\LogInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\InventarioExport;
use Maatwebsite\Excel\Facades\Excel;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventario::with(['cliente', 'servicio']);
        $clientes = \App\Models\Cliente::orderBy('nombre_completo')->get();
        $servicios = \App\Models\Servicio::orderBy('tipo_servicio')->get();

        $busqueda = $request->input('busqueda');
        $cliente_id = $request->input('cliente_id');
        $servicio_id = $request->input('servicio_id');
        $estado = $request->input('estado');

        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->whereHas('cliente', function($qc) use ($busqueda) {
                    $qc->where('nombre_completo', 'like', "%$busqueda%")
                       ->orWhere('correo', 'like', "%$busqueda%")
                       ->orWhere('telefono', 'like', "%$busqueda%") ;
                })
                ->orWhere('tracking_codigo', 'like', "%$busqueda%")
                ->orWhere('numero_guia', 'like', "%$busqueda%") ;
            });
        }
        if ($cliente_id) {
            $query->where('cliente_id', $cliente_id);
        }
        if ($servicio_id) {
            $query->where('servicio_id', $servicio_id);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }

        // Clonar query para totales globales (sin paginar)
        $queryTotales = clone $query;
        $totalPaquetes = $queryTotales->count();
        $totalEntregados = (clone $queryTotales)->where('estado', 'entregado')->count();
        $totalRecibidos = (clone $queryTotales)->where('estado', 'recibido')->count();
        $valorTotal = (clone $queryTotales)->sum('monto_calculado');

        $inventarios = $query->latest()->paginate(10)->appends($request->all());
        return view('inventario.index', compact('inventarios', 'clientes', 'servicios', 'busqueda', 'cliente_id', 'servicio_id', 'estado', 'totalPaquetes', 'totalEntregados', 'totalRecibidos', 'valorTotal'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $servicios = Servicio::all();
        return view('inventario.create', compact('clientes', 'servicios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'peso_lb'         => 'nullable|numeric',
            'volumen_pie3'    => 'nullable|numeric',
            'tarifa_manual'   => 'nullable|numeric',
            'estado'          => 'required|string|max:50',
            'numero_guia'     => 'required|string|max:100|unique:inventario,numero_guia',
            'notas'           => 'nullable|string',
            'servicio_id'     => 'nullable|exists:servicios,id',
        ], [
            'numero_guia.unique' => 'El número de guía ya está en uso. Por favor, ingresa uno diferente.'
        ]);

        $data = $request->all();
        $data['fecha_ingreso'] = now();

        // Cálculo automático del monto
        $peso = floatval($data['peso_lb'] ?? 0);
        $volumen = floatval($data['volumen_pie3'] ?? 0);
        $tarifa = null;
        if (isset($data['tarifa_manual']) && $data['tarifa_manual'] !== null && $data['tarifa_manual'] !== '') {
            $tarifa = floatval($data['tarifa_manual']);
        } else if (isset($data['cliente_id'], $data['servicio_id'])) {
            $tarifaCliente = \App\Models\TarifaCliente::where('cliente_id', $data['cliente_id'])
                ->where('servicio_id', $data['servicio_id'])
                ->first();
            $tarifa = $tarifaCliente ? floatval($tarifaCliente->tarifa) : 1.00;
        } else {
            $tarifa = 1.00;
        }
        $data['monto_calculado'] = $peso * $tarifa;

        $inventario = Inventario::create($data);

        // Log solo si es agente
        $user = Auth::user();
        if ($user && $user->rol === 'agente') {
            LogInventario::create([
                'user_id' => $user->id,
                'inventario_id' => $inventario->id,
                'accion' => 'crear',
                'antes' => null,
                'despues' => $inventario->toArray(),
            ]);
        }

        return redirect()->route('inventario.index')->with('success', 'Paquete registrado correctamente.');
    }

    public function edit($id)
    {
        $inventario = Inventario::findOrFail($id);
        $clientes = Cliente::all();
        $servicios = Servicio::all();
        return view('inventario.edit', [
            'paquete' => $inventario,
            'clientes' => $clientes,
            'servicios' => $servicios
        ]);
    }

    public function update(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);
        $antes = $inventario->toArray();

        $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'peso_lb'         => 'nullable|numeric',
            'volumen_pie3'    => 'nullable|numeric',
            'tarifa_manual'   => 'nullable|numeric',
            'estado'          => 'required|string|max:50',
            'numero_guia'     => 'required|string|max:100|unique:inventario,numero_guia,' . $inventario->id,
            'notas'           => 'nullable|string',
            'servicio_id'     => 'nullable|exists:servicios,id',
        ], [
            'numero_guia.unique' => 'El número de guía ya está en uso. Por favor, ingresa uno diferente.'
        ]);

        $data = $request->all();

        // Recalcular monto
        $peso = floatval($data['peso_lb'] ?? 0);
        $volumen = floatval($data['volumen_pie3'] ?? 0);
        $tarifa = null;
        if (isset($data['tarifa_manual']) && $data['tarifa_manual'] !== null && $data['tarifa_manual'] !== '') {
            $tarifa = floatval($data['tarifa_manual']);
        } else if (isset($data['cliente_id'], $data['servicio_id'])) {
            $tarifaCliente = \App\Models\TarifaCliente::where('cliente_id', $data['cliente_id'])
                ->where('servicio_id', $data['servicio_id'])
                ->first();
            $tarifa = $tarifaCliente ? floatval($tarifaCliente->tarifa) : 1.00;
        } else {
            $tarifa = 1.00;
        }
        $data['monto_calculado'] = $peso * $tarifa;

        $inventario->update($data);

        // Log solo si es agente
        $user = Auth::user();
        if ($user && $user->rol === 'agente') {
            LogInventario::create([
                'user_id' => $user->id,
                'inventario_id' => $inventario->id,
                'accion' => 'editar',
                'antes' => $antes,
                'despues' => $inventario->toArray(),
            ]);
        }

        // Redirigir de vuelta a la misma vista de edición con mensaje de éxito
        return redirect()->route('inventario.edit', $inventario->id)->with('success', 'Paquete actualizado correctamente.');
    }

    public function destroy($id)
    {
        $inventario = Inventario::findOrFail($id);
        $inventario->delete();

        return redirect()->route('inventario.index')->with('success', 'Paquete eliminado.');
    }

    public function show($id)
    {
        $paquete = \App\Models\Inventario::with(['cliente', 'servicio', 'factura'])->findOrFail($id);
        return view('inventario.show', compact('paquete'));
    }

    // Endpoint para obtener tarifa de cliente y servicio (AJAX)
    public function obtenerTarifa(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $servicioId = $request->input('servicio_id');
        
        \Log::info('Obteniendo tarifa para cliente: ' . $clienteId . ' y servicio: ' . $servicioId);
        
        $tarifa = \App\Models\TarifaCliente::where('cliente_id', $clienteId)
            ->where('servicio_id', $servicioId)
            ->first();
            
        $response = ['tarifa' => $tarifa ? $tarifa->tarifa : null];
        \Log::info('Tarifa encontrada: ' . json_encode($response));
        
        return response()->json($response);
    }

    public function exportExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InventarioExport, 'inventario.xlsx');
    }

    public function validarNumeroGuia(Request $request)
    {
        $numeroGuia = $request->input('numero_guia');
        $id = $request->input('id'); // Para edición
        $query = \App\Models\Inventario::where('numero_guia', $numeroGuia);
        if ($id) {
            $query->where('id', '!=', $id);
        }
        $existe = $query->exists();
        return response()->json([
            'exists' => $existe,
            'message' => $existe ? 'El número de guía ya está en uso. Por favor, ingresa uno diferente.' : ''
        ]);
    }
}
