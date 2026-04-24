<?php

namespace App\Http\Controllers;

use App\Mail\FacturaMailable;
use App\Models\Cliente;
use App\Models\ContaAsiento;
use App\Models\ContaCobro;
use App\Models\ContaCxc;
use App\Models\Encomienda;
use App\Models\Facturacion;
use App\Models\Inventario;
use App\Models\Notificacion;
use App\Models\User;
use App\Services\Contabilidad\ContabilidadFacturacionBridgeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class FacturacionController extends Controller
{
    /**
     * Vacío o solo espacios se guarda como null (número de acta opcional).
     */
    private function normalizeNumeroActaInput(Request $request): void
    {
        $raw = $request->input('numero_acta');
        if ($raw === null || $raw === '') {
            $request->merge(['numero_acta' => null]);

            return;
        }
        $trimmed = trim((string) $raw);
        $request->merge(['numero_acta' => $trimmed === '' ? null : $trimmed]);
    }

    /**
     * Los formularios envían "" en inputs number vacíos; nullable|numeric falla con "".
     * Vacío = delivery incluido en el costo (null en BD).
     */
    private function normalizeItemsDeliveryMontos(Request $request): void
    {
        $raw = $request->input('items_delivery_monto');
        if (! is_array($raw)) {
            return;
        }
        $arr = [];
        foreach ($raw as $k => $v) {
            if ($v === null) {
                $arr[$k] = null;
            } elseif (is_string($v) && trim($v) === '') {
                $arr[$k] = null;
            } else {
                $arr[$k] = $v;
            }
        }
        $request->merge(['items_delivery_monto' => $arr]);
    }

    /**
     * Inputs type="number" u ocultos vacíos envían ""; nullable|numeric falla con "".
     */
    private function normalizeOptionalNumericFormFields(Request $request): void
    {
        foreach (['delivery', 'tasa_cambio', 'monto_local', 'monto_total'] as $key) {
            if (! $request->exists($key)) {
                continue;
            }
            $v = $request->input($key);
            if ($v === null || $v === '' || (is_string($v) && trim($v) === '')) {
                $request->merge([$key => null]);
            }
        }
    }

    /** @return array<int, mixed> */
    private function numeroActaRules(Request $request, ?int $ignoreFacturaId = null): array
    {
        $rules = ['nullable', 'string', 'max:100'];
        if (! $request->filled('numero_acta')) {
            return $rules;
        }
        $unique = Rule::unique('facturacion', 'numero_acta');
        if (Schema::hasColumn('facturacion', 'anulada')) {
            $unique = $unique->where(fn ($q) => $q->where('anulada', false));
        }
        if ($ignoreFacturaId !== null) {
            $unique = $unique->ignore($ignoreFacturaId);
        }
        $rules[] = $unique;

        return $rules;
    }

    /**
     * Listar facturas con filtros persistentes
     * Los filtros se mantienen al navegar entre páginas usando ->appends($request->query())
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $facturas = Facturacion::with(['cliente', 'encomienda.remitente', 'encomienda.destinatario'])
            ->when(! Schema::hasColumn('facturacion', 'anulada') || ! $request->boolean('incluir_anuladas'), function ($q) {
                if (Schema::hasColumn('facturacion', 'anulada')) {
                    $q->noAnulada();
                }
            })
            ->when($request->filled('cliente'), function ($q) use ($request) {
                $needle = '%'.addcslashes(trim((string) $request->cliente), '%_\\').'%';
                $q->where(function ($sub) use ($needle) {
                    $sub->whereHas('cliente', function ($q2) use ($needle) {
                        $q2->where('nombre_completo', 'like', $needle);
                    })
                        ->orWhereHas('encomienda.remitente', function ($q2) use ($needle) {
                            $q2->where('nombre_completo', 'like', $needle);
                        })
                        ->orWhereHas('encomienda.destinatario', function ($q2) use ($needle) {
                            $q2->where('nombre_completo', 'like', $needle);
                        });
                });
            })
            ->when($request->filled('fecha'), function ($q) use ($request) {
                $q->where('fecha_factura', $request->fecha);
            })
            ->when($request->filled('acta'), function ($q) use ($request) {
                $q->where('numero_acta', 'like', '%'.$request->acta.'%');
            })
            ->when($request->filled('estado'), function ($q) use ($request) {
                $q->where('estado_pago', $request->estado);
            })
            ->when($request->filled('tipo_factura') && in_array($request->tipo_factura, ['paqueteria', 'encomienda_familiar'], true), function ($q) use ($request) {
                $q->where('tipo_factura', $request->tipo_factura);
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query()); // Mantiene los filtros en la paginación

        return view('facturacion.index', compact('facturas'));
    }

    // Mostrar formulario de creación
    public function create(Request $request)
    {
        $tipo = $request->query('tipo');
        if (! in_array($tipo, ['paqueteria', 'encomienda_familiar'], true)) {
            return view('facturacion.create-selector');
        }

        $clientes = \App\Models\Cliente::select('id', 'nombre_completo')->orderBy('nombre_completo')->get();
        $tipoInicial = $tipo;

        return view('facturacion.create', compact('clientes', 'tipoInicial'));
    }

    // Guardar nueva factura
    public function store(Request $request)
    {
        try {
            \Log::info('Iniciando guardado de factura', $request->all());

            $this->normalizeNumeroActaInput($request);
            $this->normalizeItemsDeliveryMontos($request);
            $this->normalizeOptionalNumericFormFields($request);

            if ($request->tipo_factura === 'encomienda_familiar') {
                $request->merge(['cliente_id' => null]);
            }

            $baseRules = [
                'cliente_id' => [
                    Rule::requiredIf(fn () => $request->tipo_factura === 'paqueteria'),
                    'nullable',
                    'exists:clientes,id',
                ],
                'fecha_factura' => 'required|date',
                'numero_acta' => $this->numeroActaRules($request),
                'tipo_factura' => 'required|in:paqueteria,encomienda_familiar',
                'moneda' => 'required|in:USD,NIO',
                'tasa_cambio' => 'nullable|numeric',
                'monto_local' => 'nullable|numeric',
                'estado_pago' => 'required|in:pendiente,parcial,pagado,entregado_pagado,entregado_sin_pagar,pagado_sin_entregar,facturado_npne',
                'nota' => 'nullable|string',
                'delivery' => 'nullable|numeric',
                'items_delivery' => 'nullable|array',
                'items_delivery.*' => 'in:1',
                'items_delivery_monto' => 'nullable|array',
                'items_delivery_monto.*' => 'nullable|numeric|min:0',
                'entrega_nombre' => 'nullable|string|max:150',
                'entrega_cedula' => 'nullable|string|max:80',
                'entrega_telefono' => 'nullable|string|max:80',
            ];
            $messages = [
                'numero_acta.unique' => 'El número de acta ya ha sido utilizado en otra factura. Por favor, ingresa un número diferente.',
            ];

            if ($request->tipo_factura === 'encomienda_familiar') {
                $encomiendaIdRules = ['required', 'exists:encomiendas,id'];
                $encomiendaIdRules[] = Schema::hasColumn('facturacion', 'anulada')
                    ? Rule::unique('facturacion', 'encomienda_id')->where(fn ($q) => $q->where('anulada', false))
                    : Rule::unique('facturacion', 'encomienda_id');
                $request->validate(array_merge($baseRules, [
                    'encomienda_id' => $encomiendaIdRules,
                    'paquetes' => 'nullable|array',
                ]), $messages);
            } else {
                $request->validate(array_merge($baseRules, [
                    'paquetes' => 'required|array|min:1',
                    'paquetes.*' => 'exists:inventario,id',
                ]), $messages);
            }

            if ($request->tipo_factura === 'encomienda_familiar') {
                $encomienda = Encomienda::with('items')->findOrFail($request->encomienda_id);
                $itemsDelivery = (array) $request->input('items_delivery', []);
                $anyDeliveryIncluded = ! empty($itemsDelivery);
                $itemsDeliveryMonto = (array) $request->input('items_delivery_monto', []);

                // Delivery total:
                // - Si hay al menos un item marcado: delivery = suma de montos por item (si viene incluido, puede ser 0).
                // - Si no hay items marcados: usar el input delivery del formulario.
                $delivery = 0.0;
                if ($anyDeliveryIncluded) {
                    foreach ($itemsDelivery as $itemId => $v) {
                        $raw = $itemsDeliveryMonto[(string) $itemId] ?? null;
                        $delivery += floatval($raw ?? 0);
                    }
                } else {
                    $delivery = floatval($request->delivery ?? 0);
                }
                $monto_total = floatval($encomienda->total) + $delivery;
                $cantidad_paquetes = max(1, $encomienda->items->count() ?: (int) ($encomienda->cantidad_bultos ?? 1));

                \Log::info('Datos para crear factura encomienda familiar', [
                    'encomienda_id' => $encomienda->id,
                    'items_delivery' => $itemsDelivery,
                    'anyDeliveryIncluded' => $anyDeliveryIncluded,
                    'items_delivery_monto' => $itemsDeliveryMonto,
                    'monto_total' => $monto_total,
                ]);

                $factura = Facturacion::create([
                    'cliente_id' => null,
                    'encomienda_id' => $encomienda->id,
                    'fecha_factura' => $request->fecha_factura,
                    'numero_acta' => $request->numero_acta,
                    'tipo_factura' => $request->tipo_factura,
                    'monto_total' => $monto_total,
                    'cantidad_paquetes' => $cantidad_paquetes,
                    'moneda' => $request->moneda,
                    'tasa_cambio' => $request->tasa_cambio,
                    'monto_local' => $request->monto_local ?? $monto_total,
                    'estado_pago' => $request->estado_pago,
                    'nota' => $request->nota,
                    'delivery' => $delivery,
                    'entrega_nombre' => $request->entrega_nombre,
                    'entrega_cedula' => $request->entrega_cedula,
                    'entrega_telefono' => $request->entrega_telefono,
                    'created_by' => Auth::id(),
                ]);

                $this->postearFacturaEnContabilidad($factura);

                // Guardar decisión de delivery por item (para que el PDF lo muestre).
                foreach ($encomienda->items as $item) {
                    $incluye = array_key_exists((string) $item->id, $itemsDelivery);
                    $item->incluye_delivery = $incluye;
                    if ($incluye) {
                        $raw = $itemsDeliveryMonto[(string) $item->id] ?? null;
                        // Si viene vacío => delivery incluido (no requiere monto)
                        $item->delivery_monto = ($raw === null || $raw === '') ? null : floatval($raw);
                    } else {
                        $item->delivery_monto = null;
                    }
                    $item->save();
                }

                // Opcional: si no hay delivery incluido, dejamos todos los items en false.
                if (! $anyDeliveryIncluded) {
                    foreach ($encomienda->items as $item) {
                        $item->incluye_delivery = false;
                        $item->delivery_monto = null;
                        $item->save();
                    }
                }

                return redirect()->route('facturacion.index')->with('success', 'Factura de encomienda familiar registrada correctamente.');
            }

            // Paquetería: validar inventario
            $paquetes = \App\Models\Inventario::whereIn('id', $request->paquetes)
                ->where('cliente_id', $request->cliente_id)
                ->whereNull('factura_id')
                ->where('estado', '!=', 'entregado')
                ->get();
            if (count($paquetes) !== count($request->paquetes)) {
                return back()->withErrors(['paquetes' => 'Uno o más paquetes seleccionados no pertenecen al cliente, ya han sido facturados o ya están entregados.'])->withInput();
            }

            $monto_total = 0;
            $cantidad_paquetes = count($paquetes);
            foreach ($paquetes as $p) {
                $peso = floatval($p->peso_lb ?? 0);
                $tarifa = floatval($p->tarifa_manual ?? $p->tarifa ?? 1);
                $monto_total += $peso * $tarifa;
            }
            $delivery = floatval($request->delivery ?? 0);
            $monto_total += $delivery;

            \Log::info('Datos para crear factura', [
                'cliente_id' => $request->cliente_id,
                'fecha_factura' => $request->fecha_factura,
                'numero_acta' => $request->numero_acta,
                'monto_total' => $monto_total,
                'cantidad_paquetes' => $cantidad_paquetes,
                'moneda' => $request->moneda,
                'tasa_cambio' => $request->tasa_cambio,
                'monto_local' => $request->monto_local ?? $monto_total,
                'estado_pago' => $request->estado_pago,
                'nota' => $request->nota,
                'delivery' => $delivery,
                'created_by' => Auth::id(),
            ]);

            $factura = Facturacion::create([
                'cliente_id' => $request->cliente_id,
                'fecha_factura' => $request->fecha_factura,
                'numero_acta' => $request->numero_acta,
                'tipo_factura' => $request->tipo_factura,
                'monto_total' => $monto_total,
                'cantidad_paquetes' => $cantidad_paquetes,
                'moneda' => $request->moneda,
                'tasa_cambio' => $request->tasa_cambio,
                'monto_local' => $request->monto_local ?? $monto_total,
                'estado_pago' => $request->estado_pago,
                'nota' => $request->nota,
                'delivery' => $delivery,
                'entrega_nombre' => $request->entrega_nombre,
                'entrega_cedula' => $request->entrega_cedula,
                'entrega_telefono' => $request->entrega_telefono,
                'created_by' => Auth::id(),
            ]);

            $this->postearFacturaEnContabilidad($factura);

            \App\Models\Inventario::whereIn('id', $paquetes->pluck('id'))->update(['factura_id' => $factura->id]);
            if (in_array($request->estado_pago, ['entregado_pagado', 'entregado_sin_pagar'])) {
                \App\Models\Inventario::whereIn('id', $paquetes->pluck('id'))->update(['estado' => 'entregado']);
            }

            return redirect()->route('facturacion.index')->with('success', 'Factura registrada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al guardar factura: '.$e->getMessage());

            return back()->withErrors(['error' => 'Error al guardar la factura: '.$e->getMessage()])->withInput();
        }
    }

    /**
     * Encomiendas sin factura (para nota de cobro familiar).
     */
    public function encomiendasDisponibles()
    {
        $ids = Facturacion::query()
            ->whereNotNull('encomienda_id')
            ->when(Schema::hasColumn('facturacion', 'anulada'), fn ($q) => $q->noAnulada())
            ->pluck('encomienda_id');

        $rows = Encomienda::query()
            ->when($ids->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $ids))
            ->with([
                'remitente:id,nombre_completo',
                'destinatario:id,nombre_completo',
                'items:id,encomienda_id,monto_total_item',
            ])
            ->orderByDesc('id')
            ->limit(300)
            ->get(['id', 'codigo', 'total', 'tipo_servicio', 'remitente_id', 'destinatario_id']);

        return response()->json([
            'success' => true,
            'encomiendas' => $rows->map(function (Encomienda $e) {
                return [
                    'id' => $e->id,
                    'codigo' => $e->codigo,
                    'total' => (float) $e->total,
                    'tipo_servicio' => $e->tipo_servicio ?? 'maritimo',
                    'remitente' => $e->remitente->nombre_completo ?? '—',
                    'destinatario' => $e->destinatario->nombre_completo ?? '—',
                    'items_count' => $e->items->count(),
                ];
            }),
        ]);
    }

    /**
     * Detalle de factura (incluye nota interna; no sustituye al PDF).
     */
    public function show(Facturacion $factura)
    {
        $factura->load([
            'cliente',
            'encomienda.remitente',
            'encomienda.destinatario',
            'encomienda.historialEstados',
            'encomienda.items',
            'paquetes.servicio',
            'creador',
            'editor',
            'anuladaPor',
        ]);

        // Solo bloquea si hubo "Registrar cobro" en Contabilidad (created_by). Los cobros importados
        // desde pagos históricos (created_by null) se pueden quitar al anular.
        $tieneCobroRegistradoUsuario = ContaCobro::query()
            ->where('factura_id', $factura->id)
            ->whereNotNull('created_by')
            ->exists();
        $puedeAnular = Schema::hasColumn('facturacion', 'anulada')
            && ! $factura->anulada
            && ! $tieneCobroRegistradoUsuario;
        $muestraSeccionAnular = Schema::hasColumn('facturacion', 'anulada') && ! $factura->anulada;
        $cobrosSoloImportados = (int) ContaCobro::query()
            ->where('factura_id', $factura->id)
            ->whereNull('created_by')
            ->count();

        return view('facturacion.show', compact('factura', 'puedeAnular', 'muestraSeccionAnular', 'cobrosSoloImportados'));
    }

    // Editar factura
    public function edit($id)
    {
        $factura = Facturacion::with(['encomienda.remitente', 'encomienda.destinatario'])->findOrFail($id);
        if (Schema::hasColumn('facturacion', 'anulada') && $factura->anulada) {
            return redirect()->route('facturacion.show', $factura->id)
                ->withErrors(['error' => 'No se puede editar una factura anulada.']);
        }
        $clientes = Cliente::all();

        return view('facturacion.edit', compact('factura', 'clientes'));
    }

    // Actualizar factura
    public function update(Request $request, $id)
    {
        $factura = Facturacion::findOrFail($id);
        if (Schema::hasColumn('facturacion', 'anulada') && $factura->anulada) {
            return redirect()->route('facturacion.show', $factura->id)
                ->withErrors(['error' => 'No se puede editar una factura anulada.']);
        }

        $this->normalizeNumeroActaInput($request);
        $this->normalizeOptionalNumericFormFields($request);

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_factura' => 'required|date',
            'numero_acta' => $this->numeroActaRules($request, $factura->id),
            'tipo_factura' => 'required|in:paqueteria,encomienda_familiar',
            'monto_total' => 'required|numeric',
            'moneda' => 'required|in:USD,NIO',
            'tasa_cambio' => 'nullable|numeric',
            'monto_local' => 'nullable|numeric',
            'estado_pago' => 'required|in:pendiente,parcial,pagado,entregado_pagado,entregado_sin_pagar,pagado_sin_entregar,facturado_npne',
            'nota' => 'nullable|string',
            'delivery' => 'nullable|numeric',
            'entrega_nombre' => 'nullable|string|max:150',
            'entrega_cedula' => 'nullable|string|max:80',
            'entrega_telefono' => 'nullable|string|max:80',
        ], [
            'numero_acta.unique' => 'El número de acta ya ha sido utilizado en otra factura. Por favor, ingresa un número diferente.',
        ]);

        $factura->update([
            'cliente_id' => $request->cliente_id,
            'fecha_factura' => $request->fecha_factura,
            'numero_acta' => $request->numero_acta,
            'tipo_factura' => $request->tipo_factura,
            'monto_total' => $request->monto_total,
            'moneda' => $request->moneda,
            'tasa_cambio' => $request->tasa_cambio,
            'monto_local' => $request->monto_local,
            'estado_pago' => $request->estado_pago,
            'nota' => $request->nota,
            'delivery' => $request->delivery,
            'entrega_nombre' => $request->entrega_nombre,
            'entrega_cedula' => $request->entrega_cedula,
            'entrega_telefono' => $request->entrega_telefono,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('facturacion.index')->with('success', 'Factura actualizada.');
    }

    // Eliminar factura
    public function destroy($id)
    {
        $factura = Facturacion::findOrFail($id);
        if (Schema::hasColumn('facturacion', 'anulada') && $factura->anulada) {
            return redirect()->route('facturacion.index')->withErrors(['error' => 'No se puede eliminar una factura anulada desde aquí.']);
        }
        if (ContaCobro::query()->where('factura_id', $factura->id)->exists()) {
            return redirect()->route('facturacion.index')->withErrors(['error' => 'No se puede eliminar: existen cobros en contabilidad para esta factura.']);
        }
        $factura->delete();

        return redirect()->route('facturacion.index')->with('success', 'Factura eliminada.');
    }

    /**
     * Anula la factura (sin cobros contables): revierte CxC/asiento de emisión, libera inventario y permite refacturar.
     */
    public function anular(Request $request, int $id)
    {
        if (! Schema::hasColumn('facturacion', 'anulada')) {
            abort(404);
        }

        $request->validate([
            'motivo' => 'nullable|string|max:2000',
        ]);

        $factura = Facturacion::findOrFail($id);

        if ($factura->anulada) {
            return redirect()->route('facturacion.show', $factura)->withErrors(['error' => 'La factura ya está anulada.']);
        }

        if (ContaCobro::query()->where('factura_id', $factura->id)->whereNotNull('created_by')->exists()) {
            return redirect()->route('facturacion.show', $factura)->withErrors([
                'error' => 'No se puede anular: ya hay cobros registrados manualmente en Contabilidad (Registrar cobro) para esta factura.',
            ]);
        }

        DB::transaction(function () use ($factura, $request) {
            $cobroIds = ContaCobro::query()->where('factura_id', $factura->id)->pluck('id');
            if ($cobroIds->isNotEmpty()) {
                ContaAsiento::query()
                    ->where('referencia_tipo', 'cobro')
                    ->whereIn('referencia_id', $cobroIds)
                    ->delete();
                ContaCobro::query()->whereIn('id', $cobroIds)->delete();
            }

            ContaAsiento::query()
                ->where('referencia_tipo', 'factura')
                ->where('referencia_id', $factura->id)
                ->delete();

            ContaCxc::query()->where('factura_id', $factura->id)->delete();

            Inventario::query()
                ->where('factura_id', $factura->id)
                ->where('estado', 'entregado')
                ->update(['estado' => 'recibido']);

            Inventario::query()->where('factura_id', $factura->id)->update(['factura_id' => null]);

            $updates = [
                'anulada' => true,
                'anulada_at' => now(),
                'anulada_por' => Auth::id(),
                'anulacion_motivo' => $request->filled('motivo') ? trim((string) $request->input('motivo')) : null,
                'numero_acta' => null,
            ];
            if (Schema::hasColumn('facturacion', 'contabilidad_pendiente')) {
                $updates['contabilidad_pendiente'] = false;
            }
            $factura->update($updates);
        });

        return redirect()->route('facturacion.show', $factura->fresh())
            ->with('success', 'Factura anulada. Los paquetes quedaron disponibles para una nueva factura; la encomienda puede volver a facturarse si aplica.');
    }

    public function descargarPDF($id)
    {
        $factura = Facturacion::with([
            'cliente',
            'paquetes.servicio',
            'encomienda.items',
            'encomienda.remitente',
            'encomienda.destinatario',
        ])->findOrFail($id);
        $view = ($factura->tipo_factura ?? 'paqueteria') === 'encomienda_familiar' ? 'facturacion.pdf-encomienda' : 'facturacion.pdf-paqueteria';
        $pdf = Pdf::loadView($view, compact('factura'));

        return $pdf->download('factura_'.$factura->id.'.pdf');
    }

    public function previsualizarPDF($id)
    {
        $factura = Facturacion::with([
            'cliente',
            'paquetes.servicio',
            'encomienda.items',
            'encomienda.remitente',
            'encomienda.destinatario',
        ])->findOrFail($id);
        $view = ($factura->tipo_factura ?? 'paqueteria') === 'encomienda_familiar' ? 'facturacion.pdf-encomienda' : 'facturacion.pdf-paqueteria';
        $pdf = Pdf::loadView($view, compact('factura'));

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
        if ($request->has('paquetes') && ! empty($request->input('paquetes'))) {
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

        $itemsDelivery = (array) $request->input('items_delivery', []);
        $anyDeliveryIncluded = ! empty($itemsDelivery);
        $itemsDeliveryMonto = (array) $request->input('items_delivery_monto', []);

        $delivery = 0.0;
        if ($anyDeliveryIncluded) {
            foreach ($itemsDelivery as $itemId => $v) {
                $raw = $itemsDeliveryMonto[(string) $itemId] ?? null;
                $delivery += floatval($raw ?? 0);
            }
        } else {
            $delivery = floatval($request->input('delivery', 0));
        }
        $factura->delivery = $delivery;
        $factura->tipo_factura = $request->input('tipo_factura', 'paqueteria');
        $factura->fecha_factura = $request->input('fecha_factura');
        $factura->numero_acta = $request->input('numero_acta');
        $factura->moneda = $request->input('moneda', 'USD');
        $factura->nota = $request->input('nota');

        if ($factura->tipo_factura === 'encomienda_familiar' && $request->filled('encomienda_id')) {
            $enc = Encomienda::with(['items', 'remitente', 'destinatario'])->find($request->input('encomienda_id'));
            if ($enc) {
                // Aplicar flags delivery por item (solo para el preview).
                foreach ($enc->items as $item) {
                    $incluye = array_key_exists((string) $item->id, $itemsDelivery);
                    $item->incluye_delivery = $incluye;
                    if ($incluye) {
                        $raw = $itemsDeliveryMonto[(string) $item->id] ?? null;
                        $item->delivery_monto = ($raw === null || $raw === '') ? null : floatval($raw);
                    } else {
                        $item->delivery_monto = null;
                    }
                }
                $factura->setRelation('encomienda', $enc);
            }
        }

        $view = $factura->tipo_factura === 'encomienda_familiar' ? 'facturacion.pdf-encomienda' : 'facturacion.pdf-paqueteria';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, compact('factura'));

        return $pdf->stream('preview.pdf');
    }

    /**
     * API: items de una encomienda (para el formulario de facturación).
     */
    public function encomiendaItems(int $id)
    {
        $encomienda = Encomienda::with(['items'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'items' => $encomienda->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tipo_item' => $item->tipo_item,
                    'descripcion' => $item->descripcion,
                    'cantidad' => (int) $item->cantidad,
                    'metodo_cobro' => $item->metodo_cobro,
                    'peso_lb' => $item->peso_lb,
                    'largo_in' => $item->largo_in,
                    'ancho_in' => $item->ancho_in,
                    'alto_in' => $item->alto_in,
                    'pie_cubico' => $item->pie_cubico,
                    'tarifa_manual' => $item->tarifa_manual,
                    'monto_total_item' => $item->monto_total_item,
                    'incluye_delivery' => (bool) ($item->incluye_delivery ?? false),
                    'delivery_monto' => $item->delivery_monto,
                ];
            }),
        ]);
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
            ->map(function ($inv) {
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
            ->map(function ($inv) {
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
        $historial = \App\Models\Facturacion::query()
            ->where('cliente_id', $clienteId)
            ->when(Schema::hasColumn('facturacion', 'anulada'), fn ($q) => $q->noAnulada())
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
        if (Schema::hasColumn('facturacion', 'anulada') && $factura->anulada) {
            return redirect()->route('facturacion.index')->withErrors(['error' => 'No se puede cambiar el estado de una factura anulada.']);
        }
        $request->validate([
            'estado_pago' => 'required|in:pendiente,parcial,pagado,entregado_pagado,entregado_sin_pagar,pagado_sin_entregar,facturado_npne',
        ]);
        $factura->estado_pago = $request->estado_pago;

        if (Schema::hasColumn('facturacion', 'contabilidad_pendiente')) {
            if ($request->estado_pago === 'entregado_pagado') {
                $cxc = ContaCxc::query()->where('factura_id', $factura->id)->first();
                $saldoCerrado = $cxc && (float) $cxc->saldo_actual <= 0.00001;
                $factura->contabilidad_pendiente = ! $saldoCerrado;
            } else {
                $factura->contabilidad_pendiente = false;
            }
        }

        $factura->save();

        if (in_array($request->estado_pago, ['entregado_pagado', 'entregado_sin_pagar'])) {
            \App\Models\Inventario::where('factura_id', $factura->id)->update(['estado' => 'entregado']);
        }

        $redirect = redirect()->route('facturacion.index')->with('success', 'Estado de la factura actualizado.');

        if (Schema::hasColumn('facturacion', 'contabilidad_pendiente')
            && $request->estado_pago === 'entregado_pagado'
            && $factura->contabilidad_pendiente) {
            $this->notificarContabilidadPendiente($factura->fresh());
            $redirect->with(
                'info_contabilidad',
                'Factura #'.$factura->id.': registre el cobro en Contabilidad (Registrar cobro) con cuenta banco/caja, método y referencia para cerrar el control contable.'
            );
        }

        return $redirect;
    }

    /**
     * Admin: marcar contabilidad como verificada sin nuevo cobro (casos excepcionales).
     */
    public function marcarContabilidadVerificada(Request $request, int $id)
    {
        if (! Auth::user() || Auth::user()->rol !== 'admin') {
            abort(403);
        }
        $factura = Facturacion::findOrFail($id);
        if (Schema::hasColumn('facturacion', 'anulada') && $factura->anulada) {
            return redirect()->route('facturacion.show', $factura->id)->withErrors(['error' => 'No aplica: la factura está anulada.']);
        }
        if (Schema::hasColumn('facturacion', 'contabilidad_pendiente')) {
            $factura->contabilidad_pendiente = false;
            $factura->save();
        }

        return back()->with('success', 'Contabilidad marcada como completada para esta factura.');
    }

    public function enviarCorreo($id)
    {
        $factura = Facturacion::with([
            'cliente',
            'paquetes.servicio',
            'encomienda.items',
            'encomienda.remitente',
            'encomienda.destinatario',
        ])->findOrFail($id);
        $correo = $factura->cliente?->correo;
        if (! $correo) {
            return back()->with('error', 'El cliente no tiene correo.');
        }
        // Generar PDF temporal
        $view = ($factura->tipo_factura ?? 'paqueteria') === 'encomienda_familiar' ? 'facturacion.pdf-encomienda' : 'facturacion.pdf-paqueteria';
        $pdf = \PDF::loadView($view, ['factura' => $factura]);
        $pdfContent = $pdf->output();
        // Enviar correo
        Mail::to($correo)->send(new FacturaMailable($factura, $pdfContent));

        return back()->with('success', 'Factura enviada correctamente a '.$correo);
    }

    public function validarNumeroActa(Request $request)
    {
        $numeroActa = trim((string) $request->input('numero_acta', ''));
        if ($numeroActa === '') {
            return response()->json([
                'exists' => false,
                'message' => '',
            ]);
        }

        $facturaId = $request->input('factura_id'); // Para edición

        $query = Facturacion::query()->where('numero_acta', $numeroActa);
        if (Schema::hasColumn('facturacion', 'anulada')) {
            $query->noAnulada();
        }

        if ($facturaId) {
            $query->where('id', '!=', $facturaId);
        }

        $existe = $query->exists();

        return response()->json([
            'exists' => $existe,
            'message' => $existe ? 'El número de acta ya ha sido utilizado en otra factura. Por favor, ingresa un número diferente.' : '',
        ]);
    }

    private function postearFacturaEnContabilidad(Facturacion $factura): void
    {
        try {
            app(ContabilidadFacturacionBridgeService::class)->registrarFactura($factura);
        } catch (\Throwable $e) {
            \Log::warning('No se pudo contabilizar factura automáticamente', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function notificarContabilidadPendiente(Facturacion $factura): void
    {
        $destinatarios = User::query()
            ->whereIn('rol', ['admin', 'auditor'])
            ->where('estado', 1)
            ->pluck('id');

        if ($destinatarios->isEmpty()) {
            return;
        }

        $urlFactura = route('facturacion.show', $factura->id);
        $urlCobro = route('contabilidad.cobros.create', ['factura_id' => $factura->id]);
        $titulo = 'Contabilidad pendiente — factura #'.$factura->id;
        $mensaje = 'La factura #'.$factura->id.' quedó en estado Entregado y pagado. Debe registrarse el cobro en Contabilidad (cuenta, método, referencia).'
            ."\nVer factura: {$urlFactura}"
            ."\nRegistrar cobro: {$urlCobro}";

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
}
