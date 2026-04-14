<?php

namespace App\Http\Controllers;

use App\Models\Destinatario;
use App\Models\Encomienda;
use App\Models\EncomiendaHistorialEstado;
use App\Models\EncomiendaItem;
use App\Models\Remitente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EncomiendaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');
        $estado = $request->input('estado');
        $query = Encomienda::with(['remitente', 'destinatario']);

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                    ->orWhereHas('remitente', fn ($sub) => $sub->where('nombre_completo', 'like', "%{$busqueda}%"))
                    ->orWhereHas('destinatario', fn ($sub) => $sub->where('nombre_completo', 'like', "%{$busqueda}%"));
            });
        }

        if ($estado) {
            $query->where('estado_actual', $estado);
        }

        $encomiendas = $query->latest()->paginate(10)->appends($request->all());
        $totales = [
            'total' => Encomienda::count(),
            'registradas' => Encomienda::where('estado_actual', 'registrada')->count(),
            'entregadas' => Encomienda::where('estado_actual', 'entregada')->count(),
            'monto' => Encomienda::sum('total'),
        ];

        return view('encomiendas.index', compact('encomiendas', 'busqueda', 'estado', 'totales'));
    }

    public function create()
    {
        $remitentes = Remitente::orderBy('nombre_completo')->get();
        $destinatarios = Destinatario::orderBy('nombre_completo')->get();

        return view('encomiendas.create', compact('remitentes', 'destinatarios'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $this->validateItemFotosCounts($request, $validated, null);
        $tipoServicio = $validated['tipo_servicio'];

        DB::transaction(function () use ($validated, $tipoServicio, $request) {
            $totales = $this->calcularTotalesItems($validated['items'], $tipoServicio);
            $remitenteId = $this->resolveRemitenteId($validated);
            $destinatarioId = $this->resolveDestinatarioId($validated);

            $numItems = count($totales['items']);

            $encomienda = Encomienda::create([
                'codigo' => $this->nextCodigo(),
                'remitente_id' => $remitenteId,
                'destinatario_id' => $destinatarioId,
                'estado_actual' => 'registrada',
                'tipo_servicio' => $tipoServicio,
                'cantidad_bultos' => max(1, $numItems),
                'valor_declarado' => null,
                'descripcion_general' => $validated['descripcion_general'] ?? null,
                'observaciones' => null,
                'subtotal' => $totales['subtotal'],
                'total' => $totales['total'],
                'created_by' => Auth::id(),
            ]);

            foreach ($totales['items'] as $idx => $item) {
                $itemData = $item;
                $paths = $this->storeNewItemFotos($request, $idx);
                if ($paths !== null) {
                    $itemData['foto_paths'] = $paths;
                }
                $encomienda->items()->create($itemData);
            }

            EncomiendaHistorialEstado::create([
                'encomienda_id' => $encomienda->id,
                'estado' => 'registrada',
                'comentario' => 'Encomienda registrada.',
                'user_id' => Auth::id(),
                'fecha_cambio' => now(),
            ]);
        });

        return redirect()->route('encomiendas.index')->with('success', 'Encomienda registrada correctamente.');
    }

    public function show($id)
    {
        $encomienda = Encomienda::with([
            'remitente',
            'destinatario',
            'items',
            'historialEstados.usuario',
            'factura',
            'creador',
            'editor',
        ])->findOrFail($id);

        return view('encomiendas.show', compact('encomienda'));
    }

    /**
     * Sirve una foto del bulto (índice 0, 1 o 2) por la app (mismo host/puerto que artisan serve).
     */
    public function itemFoto(Encomienda $encomienda, EncomiendaItem $item, int $index)
    {
        if ((int) $item->encomienda_id !== (int) $encomienda->id) {
            abort(404);
        }
        $paths = $item->fotoPathsList();
        if (! array_key_exists($index, $paths)) {
            abort(404);
        }
        $path = $paths[$index];
        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path, basename($path), [
            'Content-Disposition' => 'inline',
        ]);
    }

    public function edit($id)
    {
        $encomienda = Encomienda::with('items')->findOrFail($id);
        $remitentes = Remitente::orderBy('nombre_completo')->get();
        $destinatarios = Destinatario::orderBy('nombre_completo')->get();

        return view('encomiendas.edit', compact('encomienda', 'remitentes', 'destinatarios'));
    }

    public function update(Request $request, $id)
    {
        $encomienda = Encomienda::with('items')->findOrFail($id);
        $validated = $this->validateRequest($request);
        $this->validateItemFotosCounts($request, $validated, $encomienda);
        $tipoServicio = $validated['tipo_servicio'];

        DB::transaction(function () use ($validated, $encomienda, $tipoServicio, $request) {
            $totales = $this->calcularTotalesItems($validated['items'], $tipoServicio);
            $remitenteId = $this->resolveRemitenteId($validated);
            $destinatarioId = $this->resolveDestinatarioId($validated);

            $numItems = count($totales['items']);

            $encomienda->update([
                'remitente_id' => $remitenteId,
                'destinatario_id' => $destinatarioId,
                'tipo_servicio' => $tipoServicio,
                'cantidad_bultos' => max(1, $numItems),
                'valor_declarado' => null,
                'descripcion_general' => $validated['descripcion_general'] ?? null,
                'observaciones' => null,
                'subtotal' => $totales['subtotal'],
                'total' => $totales['total'],
                'updated_by' => Auth::id(),
            ]);

            $oldItemsById = $encomienda->items()->get()->keyBy('id');
            $pathsToDelete = [];

            $encomienda->items()->delete();
            foreach ($totales['items'] as $idx => $item) {
                $itemData = $item;
                $preserveId = $validated['items'][$idx]['preserve_item_id'] ?? null;
                $previous = $preserveId ? ($oldItemsById->get((int) $preserveId)) : null;

                $merged = $this->mergeItemFotosOnUpdate($request, $idx, $previous, $pathsToDelete);
                if ($merged !== null) {
                    $itemData['foto_paths'] = $merged;
                }

                $encomienda->items()->create($itemData);
            }

            foreach ($pathsToDelete as $path) {
                Storage::disk('public')->delete($path);
            }
        });

        return redirect()->route('encomiendas.show', $encomienda->id)->with('success', 'Encomienda actualizada correctamente.');
    }

    public function destroy($id)
    {
        $encomienda = Encomienda::findOrFail($id);
        $encomienda->delete();

        return redirect()->route('encomiendas.index')->with('success', 'Encomienda eliminada correctamente.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'remitente_id' => 'nullable|exists:remitentes,id',
            'destinatario_id' => 'nullable|exists:destinatarios,id',
            'nuevo_remitente' => 'nullable|boolean',
            'nuevo_destinatario' => 'nullable|boolean',
            'remitente.nombre_completo' => 'nullable|string|max:255',
            'remitente.telefono' => 'nullable|string|max:30',
            'remitente.correo' => 'nullable|email|max:255',
            'remitente.direccion' => 'nullable|string|max:255',
            'remitente.ciudad' => 'nullable|string|max:120',
            'remitente.estado' => 'nullable|string|max:120',
            'remitente.identificacion' => 'nullable|string|max:120',
            'destinatario.nombre_completo' => 'nullable|string|max:255',
            'destinatario.telefono_1' => 'nullable|string|max:30',
            'destinatario.telefono_2' => 'nullable|string|max:30',
            'destinatario.direccion' => 'nullable|string|max:255',
            'destinatario.referencias' => 'nullable|string|max:255',
            'destinatario.ciudad' => 'nullable|string|max:120',
            'destinatario.departamento' => 'nullable|string|max:120',
            'destinatario.cedula' => 'nullable|string|max:120',
            'destinatario.autorizado_para_recibir' => 'nullable|boolean',
            'tipo_servicio' => 'required|in:aereo,maritimo',
            'descripcion_general' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.tipo_item' => 'required|string|max:120',
            'items.*.descripcion' => 'nullable|string|max:255',
            'items.*.cantidad' => 'nullable|integer|in:1',
            'items.*.metodo_cobro' => 'required|in:peso,pie_cubico',
            'items.*.peso_lb' => 'nullable|numeric|min:0',
            'items.*.largo_in' => 'nullable|numeric|min:0',
            'items.*.ancho_in' => 'nullable|numeric|min:0',
            'items.*.alto_in' => 'nullable|numeric|min:0',
            'items.*.tarifa_manual' => 'nullable|numeric|min:0',
            'items.*.total_linea_pie_cubico' => 'nullable|numeric|min:0',
            'items.*.preserve_item_id' => 'nullable|integer|exists:encomienda_items,id',
            'items.*.keep_foto_paths' => 'nullable|array|max:3',
            'items.*.keep_foto_paths.*' => 'string|max:512',
            'items.*.fotos' => 'nullable|array|max:3',
            'items.*.fotos.*' => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);
    }

    private function validateItemFotosCounts(Request $request, array $validated, ?Encomienda $encomiendaParaEdit): void
    {
        $oldById = $encomiendaParaEdit
            ? $encomiendaParaEdit->items->keyBy('id')
            : collect();

        foreach ($validated['items'] as $idx => $item) {
            $files = $request->file("items.{$idx}.fotos");
            $nNew = 0;
            if (is_array($files)) {
                $nNew = count(array_filter($files, fn ($f) => $f && $f->isValid()));
            } elseif ($files && $files->isValid()) {
                $nNew = 1;
            }
            if ($nNew > 3) {
                throw ValidationException::withMessages([
                    "items.{$idx}.fotos" => 'Máximo 3 fotos nuevas por bulto.',
                ]);
            }

            $legitKeep = 0;
            if ($oldById->isNotEmpty()) {
                $preserveId = $item['preserve_item_id'] ?? null;
                $prev = $preserveId ? $oldById->get((int) $preserveId) : null;
                $prevPaths = $prev ? $prev->fotoPathsList() : [];
                $keepReq = array_values(array_filter(
                    $request->input("items.{$idx}.keep_foto_paths", []),
                    fn ($p) => is_string($p) && $p !== ''
                ));
                $legitKeep = count(array_intersect($keepReq, $prevPaths));
            }

            if ($legitKeep + $nNew > 3) {
                throw ValidationException::withMessages([
                    "items.{$idx}.fotos" => 'Máximo 3 fotos por bulto (las que conservas más las nuevas).',
                ]);
            }
        }
    }

    /** @return list<string>|null */
    private function storeNewItemFotos(Request $request, int $idx): ?array
    {
        $files = $request->file("items.{$idx}.fotos");
        $paths = [];
        if (is_array($files)) {
            foreach (array_slice($files, 0, 3) as $f) {
                if ($f && $f->isValid()) {
                    $paths[] = $f->store('encomiendas/items', 'public');
                }
            }
        } elseif ($files && $files->isValid()) {
            $paths[] = $files->store('encomiendas/items', 'public');
        }

        return $paths === [] ? null : $paths;
    }

    /**
     * @param  array<string>  $pathsToDelete  Referencia: rutas de disco a borrar tras el guardado.
     * @return list<string>|null
     */
    private function mergeItemFotosOnUpdate(Request $request, int $idx, ?EncomiendaItem $previous, array &$pathsToDelete): ?array
    {
        $prevPaths = $previous ? $previous->fotoPathsList() : [];
        $keepReq = array_values(array_filter(
            $request->input("items.{$idx}.keep_foto_paths", []),
            fn ($p) => is_string($p) && $p !== ''
        ));
        $keep = array_values(array_intersect($keepReq, $prevPaths));

        $files = $request->file("items.{$idx}.fotos");
        $newPaths = [];
        if (is_array($files)) {
            foreach ($files as $f) {
                if ($f && $f->isValid()) {
                    $newPaths[] = $f->store('encomiendas/items', 'public');
                }
            }
        } elseif ($files && $files->isValid()) {
            $newPaths[] = $files->store('encomiendas/items', 'public');
        }

        $slots = max(0, 3 - count($keep));
        $newPaths = array_slice($newPaths, 0, $slots);
        $merged = array_merge($keep, $newPaths);

        foreach (array_diff($prevPaths, $merged) as $p) {
            $pathsToDelete[] = $p;
        }

        return $merged === [] ? null : $merged;
    }

    private function resolveRemitenteId(array $validated): int
    {
        $nuevo = in_array($validated['nuevo_remitente'] ?? null, [1, '1', true, 'true'], true);
        $remitenteData = $validated['remitente'] ?? [];

        if ($nuevo) {
            if (empty($remitenteData['nombre_completo']) || empty($remitenteData['telefono'])) {
                throw ValidationException::withMessages([
                    'remitente' => 'Si marcas nuevo remitente, debes completar nombre y telefono.',
                ]);
            }

            $model = Remitente::create([
                'nombre_completo' => $remitenteData['nombre_completo'],
                'telefono' => $remitenteData['telefono'],
                'correo' => $remitenteData['correo'] ?? null,
                'direccion' => $remitenteData['direccion'] ?? null,
                'ciudad' => $remitenteData['ciudad'] ?? null,
                'estado' => $remitenteData['estado'] ?? null,
                'identificacion' => $remitenteData['identificacion'] ?? null,
                'created_by' => Auth::id(),
            ]);

            return $model->id;
        }

        if (empty($validated['remitente_id'])) {
            throw ValidationException::withMessages([
                'remitente_id' => 'Debes seleccionar un remitente o crear uno nuevo.',
            ]);
        }

        return (int) $validated['remitente_id'];
    }

    private function resolveDestinatarioId(array $validated): int
    {
        $nuevo = in_array($validated['nuevo_destinatario'] ?? null, [1, '1', true, 'true'], true);
        $destinatarioData = $validated['destinatario'] ?? [];

        if ($nuevo) {
            if (empty($destinatarioData['nombre_completo']) || empty($destinatarioData['telefono_1']) || empty($destinatarioData['direccion'])) {
                throw ValidationException::withMessages([
                    'destinatario' => 'Si marcas nuevo destinatario, debes completar nombre, telefono principal y direccion.',
                ]);
            }

            $model = Destinatario::create([
                'nombre_completo' => $destinatarioData['nombre_completo'],
                'telefono_1' => $destinatarioData['telefono_1'],
                'telefono_2' => $destinatarioData['telefono_2'] ?? null,
                'direccion' => $destinatarioData['direccion'],
                'referencias' => $destinatarioData['referencias'] ?? null,
                'ciudad' => $destinatarioData['ciudad'] ?? null,
                'departamento' => $destinatarioData['departamento'] ?? null,
                'cedula' => $destinatarioData['cedula'] ?? null,
                'autorizado_para_recibir' => (bool) ($destinatarioData['autorizado_para_recibir'] ?? true),
                'created_by' => Auth::id(),
            ]);

            return $model->id;
        }

        if (empty($validated['destinatario_id'])) {
            throw ValidationException::withMessages([
                'destinatario_id' => 'Debes seleccionar un destinatario o crear uno nuevo.',
            ]);
        }

        return (int) $validated['destinatario_id'];
    }

    private function calcularTotalesItems(array $items, string $tipoServicio): array
    {
        $subtotal = 0;
        $resultItems = [];

        foreach ($items as $item) {
            $cantidad = 1; // Regla de negocio: una fila = un bulto/unidad
            $metodo = $tipoServicio === 'aereo' ? 'peso' : $item['metodo_cobro'];

            $peso = null;
            $largo = null;
            $ancho = null;
            $alto = null;
            $pieCubico = null;
            $tarifa = 0.0;
            $montoTotalItem = 0.0;

            if ($metodo === 'peso') {
                $tarifa = (float) ($item['tarifa_manual'] ?? 0);
                if ($tarifa <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Cada ítem por peso requiere una tarifa ($/lb) mayor a 0.',
                    ]);
                }
                $peso = (float) ($item['peso_lb'] ?? 0);
                if ($peso <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Cada ítem con cobro por peso requiere un peso mayor a 0.',
                    ]);
                }
                $montoTotalItem = round($peso * $tarifa * $cantidad, 2);
            } else {
                $largo = (float) ($item['largo_in'] ?? 0);
                $ancho = (float) ($item['ancho_in'] ?? 0);
                $alto = (float) ($item['alto_in'] ?? 0);
                if ($largo <= 0 || $ancho <= 0 || $alto <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Cada ítem por pie cúbico requiere largo, ancho y alto mayores a 0.',
                    ]);
                }
                $pieCubico = round(($largo * $ancho * $alto) / 1728, 4);
                if ($pieCubico <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'No se pudo calcular el volumen del ítem por pie cúbico.',
                    ]);
                }

                // Interpretación:
                // - total_linea_pie_cubico = $ para 1 unidad (cantidad=1) con esas dimensiones
                // - el total real se calcula multiplicando por $cantidad
                $totalUnit = round((float) ($item['total_linea_pie_cubico'] ?? 0), 2);
                if ($totalUnit <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Cada ítem por pie cúbico requiere un total unitario ($) mayor a 0.',
                    ]);
                }

                $montoTotalItem = round($totalUnit * $cantidad, 2);
                $tarifa = round($totalUnit / $pieCubico, 6); // tarifa implícita por ft³
            }

            $subtotal += $montoTotalItem;

            $resultItems[] = [
                'tipo_item' => $item['tipo_item'],
                'descripcion' => $item['descripcion'] ?? null,
                'cantidad' => $cantidad,
                'metodo_cobro' => $metodo,
                'peso_lb' => $peso,
                'largo_in' => $largo,
                'ancho_in' => $ancho,
                'alto_in' => $alto,
                'pie_cubico' => $pieCubico,
                'tarifa_manual' => $tarifa,
                'monto_total_item' => $montoTotalItem,
            ];
        }

        return [
            'items' => $resultItems,
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2),
        ];
    }

    private function nextCodigo(): string
    {
        $ultimo = Encomienda::latest('id')->first();
        $next = ($ultimo?->id ?? 0) + 1;

        return 'EC-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
