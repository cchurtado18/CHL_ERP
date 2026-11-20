<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ContaCxp;
use App\Models\Inventario;
use App\Models\LogInventario;
use App\Models\Notificacion;
use App\Models\Proveedor;
use App\Models\Servicio;
use App\Models\User;
use App\Services\Contabilidad\ContabilidadCxpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventario::with(['cliente', 'servicio', 'proveedor']);
        $clientes = Cliente::orderBy('nombre_completo')->get(['id', 'nombre_completo']);
        $servicios = \App\Models\Servicio::orderBy('tipo_servicio')->get();

        $busqueda = $request->input('busqueda');
        $cliente_id = $request->input('cliente_id');
        $servicio_id = $request->input('servicio_id');
        $estado = $request->input('estado');
        $facturacion = $request->input('facturacion');

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('cliente', function ($qc) use ($busqueda) {
                    $qc->where('nombre_completo', 'like', "%$busqueda%")
                        ->orWhere('correo', 'like', "%$busqueda%")
                        ->orWhere('telefono', 'like', "%$busqueda%");
                })
                    ->orWhere('tracking_codigo', 'like', "%$busqueda%")
                    ->orWhere('numero_guia', 'like', "%$busqueda%");
                if (Schema::hasColumn('inventario', 'factura_proveedor_codigo')) {
                    $q->orWhere('factura_proveedor_codigo', 'like', "%$busqueda%");
                }
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
        if ($facturacion === 'no_facturados') {
            $query->whereNull('factura_id');
        } elseif ($facturacion === 'facturados') {
            $query->whereNotNull('factura_id');
        }

        // Clonar query para totales globales (sin paginar)
        $queryTotales = clone $query;
        $totalPaquetes = $queryTotales->count();
        $totalEntregados = (clone $queryTotales)->where('estado', 'entregado')->count();
        $totalRecibidos = (clone $queryTotales)->where('estado', 'recibido')->count();
        $valorTotal = (clone $queryTotales)->sum('monto_calculado');
        $totalNoFacturados = Inventario::whereNull('factura_id')->count();

        $inventarios = $query->latest()->paginate(10)->appends($request->all());

        return view('inventario.index', compact('inventarios', 'clientes', 'servicios', 'busqueda', 'cliente_id', 'servicio_id', 'estado', 'facturacion', 'totalPaquetes', 'totalEntregados', 'totalRecibidos', 'valorTotal', 'totalNoFacturados'));
    }

    public function create(Request $request)
    {
        if ($request->boolean('cxp_reset')) {
            $request->session()->forget('inventario_cxp');

            return redirect()->route('inventario.create');
        }

        $clientes = Cliente::orderBy('nombre_completo')->get(['id', 'nombre_completo']);
        $servicios = Servicio::all();
        $proveedores = $this->proveedoresActivos();
        $facturasCxp = $this->facturasProveedorDisponibles();
        $facturasCxpJs = $this->facturasCxpJs($facturasCxp);
        $cxpPrefill = $this->cxpPrefill($request);

        return view('inventario.create', compact('clientes', 'servicios', 'proveedores', 'facturasCxp', 'facturasCxpJs', 'cxpPrefill'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'peso_lb' => 'nullable|numeric|min:0|decimal:0,6',
            'volumen_pie3' => 'nullable|numeric',
            'tarifa_manual' => 'nullable|numeric|min:0',
            'estado' => 'required|string|max:50',
            'numero_guia' => 'required|string|min:6|max:9|unique:inventario,numero_guia',
            'notas' => 'nullable|string',
            'servicio_id' => 'nullable|exists:servicios,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'cxp_id' => 'nullable|exists:conta_cxp,id',
        ], [
            'peso_lb.decimal' => 'El peso se guarda como lo ingreses. Si usas decimales extra, máximo 4.',
            'numero_guia.unique' => 'El número de guía ya está en uso. Por favor, ingresa uno diferente.',
            'numero_guia.min' => 'El número de guía debe tener al menos 6 caracteres.',
            'numero_guia.max' => 'El número de guía no puede exceder 9 caracteres.',
        ]);

        // Validación personalizada para el formato del número de guía
        $numeroGuia = $request->input('numero_guia');
        if (! preg_match('/^(\d+\/\d+|\d{6,9})$/', $numeroGuia)) {
            return back()->withErrors([
                'numero_guia' => 'El número de guía debe tener 6-9 dígitos o formato con barra (ej: 1223113/1)',
            ])->withInput();
        }

        $data = $request->all();
        $data['fecha_ingreso'] = now();
        $data['cxp_id'] = $request->filled('cxp_id') ? (int) $request->input('cxp_id') : null;
        $this->aplicarFacturaProveedor($data);

        $data['peso_lb'] = round((float) ($data['peso_lb'] ?? 0), 4);
        $data['monto_calculado'] = $this->montoDesdePesoYTarifa($data);

        $inventario = Inventario::create($data);
        $this->recordarFacturaProveedor($request, $data);
        $this->vincularCxpSiAplica($inventario);

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

        if (! empty($data['cxp_id'])) {
            $folio = $data['factura_proveedor_codigo'] ?? 'proveedor';

            return redirect()
                ->route('inventario.create')
                ->with('success', 'Paquete '.$inventario->numero_guia.' guardado en la factura '.$folio.'. Sigue con el siguiente (puedes entrarlos en varios días).');
        }

        return redirect()->route('inventario.index')->with('success', 'Paquete registrado correctamente.');
    }

    public function edit($id)
    {
        $inventario = Inventario::findOrFail($id);
        $clientes = Cliente::orderBy('nombre_completo')->get(['id', 'nombre_completo']);
        $servicios = Servicio::all();
        $proveedores = $this->proveedoresActivos();
        $facturasCxp = $this->facturasProveedorDisponibles($inventario->cxp_id);

        return view('inventario.edit', [
            'paquete' => $inventario,
            'clientes' => $clientes,
            'servicios' => $servicios,
            'proveedores' => $proveedores,
            'facturasCxp' => $facturasCxp,
        ]);
    }

    public function update(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);
        $antes = $inventario->toArray();

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'peso_lb' => 'nullable|numeric|min:0|decimal:0,6',
            'volumen_pie3' => 'nullable|numeric',
            'tarifa_manual' => 'nullable|numeric|min:0',
            'estado' => 'required|string|max:50',
            'numero_guia' => 'required|string|min:6|max:9|unique:inventario,numero_guia,'.$inventario->id,
            'notas' => 'nullable|string',
            'servicio_id' => 'nullable|exists:servicios,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'cxp_id' => 'nullable|exists:conta_cxp,id',
        ], [
            'peso_lb.decimal' => 'El peso se guarda como lo ingreses. Si usas decimales extra, máximo 4.',
            'numero_guia.unique' => 'El número de guía ya está en uso. Por favor, ingresa uno diferente.',
            'numero_guia.min' => 'El número de guía debe tener al menos 6 caracteres.',
            'numero_guia.max' => 'El número de guía no puede exceder 9 caracteres.',
        ]);

        // Validación personalizada para el formato del número de guía
        $numeroGuia = $request->input('numero_guia');
        if (! preg_match('/^(\d+\/\d+|\d{6,9})$/', $numeroGuia)) {
            return back()->withErrors([
                'numero_guia' => 'El número de guía debe tener 6-9 dígitos o formato con barra (ej: 1223113/1)',
            ])->withInput();
        }

        $data = $request->all();
        $data['cxp_id'] = $request->filled('cxp_id') ? (int) $request->input('cxp_id') : null;
        $this->aplicarFacturaProveedor($data);

        $data['peso_lb'] = round((float) ($data['peso_lb'] ?? 0), 4);
        $data['monto_calculado'] = $this->montoDesdePesoYTarifa($data);

        $inventario->update($data);
        $this->vincularCxpSiAplica($inventario->fresh());

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

            $this->notificarEdicionAgente($inventario, $user);
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
        $paquete = \App\Models\Inventario::with(['cliente', 'servicio', 'factura', 'proveedor', 'cxp'])->findOrFail($id);

        return view('inventario.show', compact('paquete'));
    }

    // Endpoint para obtener tarifa de cliente y servicio (AJAX)
    public function obtenerTarifa(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $servicioId = $request->input('servicio_id');

        \Log::info('Obteniendo tarifa para cliente: '.$clienteId.' y servicio: '.$servicioId);

        $tarifa = \App\Models\TarifaCliente::where('cliente_id', $clienteId)
            ->where('servicio_id', $servicioId)
            ->first();

        $response = ['tarifa' => $tarifa ? $tarifa->tarifa : null];
        \Log::info('Tarifa encontrada: '.json_encode($response));

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
            'message' => $existe ? 'El número de guía ya está en uso. Por favor, ingresa uno diferente.' : '',
        ]);
    }

    /**
     * Monto = peso × tarifa, redondeado a 2 decimales (dinero).
     * El peso puede llevar hasta 4 decimales; no se trunca antes de multiplicar.
     *
     * @param  array<string, mixed>  $data
     */
    private function montoDesdePesoYTarifa(array &$data): float
    {
        $peso = (float) ($data['peso_lb'] ?? 0);
        $tarifa = null;

        if (isset($data['tarifa_manual']) && $data['tarifa_manual'] !== null && $data['tarifa_manual'] !== '') {
            $tarifa = (float) $data['tarifa_manual'];
        } elseif (isset($data['cliente_id'], $data['servicio_id'])) {
            $tarifaCliente = \App\Models\TarifaCliente::where('cliente_id', $data['cliente_id'])
                ->where('servicio_id', $data['servicio_id'])
                ->first();

            if ($tarifaCliente) {
                $tarifa = (float) $tarifaCliente->tarifa;
                $data['tarifa_manual'] = $tarifa;
            }
        }

        $tarifa = $tarifa ?? 1.00;

        return Inventario::calcularMonto($peso, $tarifa);
    }

    private function notificarEdicionAgente(Inventario $inventario, User $agente): void
    {
        $destinatarios = User::query()
            ->whereIn('rol', ['admin', 'auditor'])
            ->where('estado', 1)
            ->pluck('id');

        if ($destinatarios->isEmpty()) {
            return;
        }

        $nombreAgente = $agente->nombre ?: $agente->email;
        $urlPaquete = route('inventario.show', $inventario->id);
        $titulo = 'Edición de paquete por agente';
        $mensaje = "El agente {$nombreAgente} editó el paquete #{$inventario->id}".
            ($inventario->numero_guia ? " (guía {$inventario->numero_guia})" : '').
            ". Revisa el historial de inventario para validar los cambios.\n".
            "Ver paquete: {$urlPaquete}";

        foreach ($destinatarios as $userId) {
            Notificacion::create([
                'user_id' => $userId,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'leido' => 0,
                'fecha' => now(),
            ]);
        }
    }

    private function proveedoresActivos()
    {
        if (! Schema::hasTable('proveedores')) {
            return collect();
        }

        return Proveedor::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    /**
     * @return array{proveedor_id:?string,cxp_id:?string}
     */
    private function cxpPrefill(Request $request): array
    {
        $saved = $request->session()->get('inventario_cxp', []);

        return [
            'proveedor_id' => old('proveedor_id', $saved['proveedor_id'] ?? null),
            'cxp_id' => old('cxp_id', $saved['cxp_id'] ?? null),
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function aplicarFacturaProveedor(array &$data): void
    {
        if (empty($data['cxp_id']) || ! Schema::hasTable('conta_cxp')) {
            $data['cxp_id'] = null;
            $data['factura_proveedor_codigo'] = null;

            return;
        }

        $cxp = ContaCxp::query()->find($data['cxp_id']);
        if (! $cxp) {
            $data['cxp_id'] = null;
            $data['factura_proveedor_codigo'] = null;

            return;
        }

        $data['proveedor_id'] = $cxp->proveedor_id;
        $data['factura_proveedor_codigo'] = $cxp->numero_documento;
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function recordarFacturaProveedor(Request $request, array $data): void
    {
        $request->session()->put('inventario_cxp', [
            'proveedor_id' => $data['proveedor_id'] ?? null,
            'cxp_id' => $data['cxp_id'] ?? null,
        ]);
    }

    private function facturasProveedorDisponibles(?int $incluirId = null)
    {
        if (! Schema::hasTable('conta_cxp')) {
            return collect();
        }

        return ContaCxp::query()
            ->with('proveedor')
            ->withSum('paquetes as monto_paquetes', 'monto_calculado')
            ->where(function ($q) use ($incluirId) {
                $q->where('estado_pago', '!=', 'pagada')
                    ->orWhere('saldo_actual', '>', 0);
                if ($incluirId) {
                    $q->orWhereKey($incluirId);
                }
            })
            ->whereNotNull('numero_documento')
            ->where('numero_documento', '!=', '')
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int,\App\Models\ContaCxp>  $facturas
     * @return list<array<string,mixed>>
     */
    private function facturasCxpJs($facturas): array
    {
        return $facturas->map(function ($f) {
            return [
                'id' => $f->id,
                'proveedor_id' => $f->proveedor_id,
                'numero' => $f->etiquetaDocumento(),
                'proveedor' => $f->proveedor->nombre ?? 'Proveedor',
                'monto' => (float) $f->monto_original,
                'asignado' => $f->montoPaquetesAsignados(),
            ];
        })->values()->all();
    }

    private function vincularCxpSiAplica(Inventario $paquete): void
    {
        try {
            app(ContabilidadCxpService::class)->sincronizarPaquete($paquete);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
