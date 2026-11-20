<?php

namespace App\Http\Controllers;

use App\Models\CargotrackAgencia;
use App\Models\CargotrackInbound;
use App\Models\CargotrackSetting;
use App\Models\Cliente;
use App\Services\Tracking\TrackingSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CargoTrackAdminController extends Controller
{
    public function index(Request $request)
    {
        $tab = $this->resolveTab($request->query('tab'));
        $settings = CargotrackSetting::current();
        $agencias = CargotrackAgencia::with('cliente')->orderBy('codigo')->get();
        $clientes = Cliente::orderBy('nombre_completo')->get(['id', 'nombre_completo']);

        $stats = [
            'total' => CargotrackInbound::count(),
            'assigned' => CargotrackInbound::where('assign_status', 'assigned')->count(),
            'unmapped' => CargotrackInbound::whereIn('assign_status', ['unmapped', 'pending'])->count(),
            'with_docs' => CargotrackInbound::whereNotNull('documento_path')->count(),
            'en_inventario' => CargotrackInbound::whereNotNull('inventario_id')->count(),
        ];

        $inbound = null;
        $agenciaCodigos = collect();

        if ($tab === 'inventario') {
            $query = CargotrackInbound::with(['cliente', 'inventario'])
                ->orderByDesc('synced_at')
                ->orderByDesc('id');

            if ($q = trim((string) $request->query('q', ''))) {
                $query->where(function ($builder) use ($q) {
                    $builder->where('warehouse', 'like', "%{$q}%")
                        ->orWhere('tracking_codigo', 'like', "%{$q}%")
                        ->orWhere('consignee_nombre', 'like', "%{$q}%")
                        ->orWhere('shipper', 'like', "%{$q}%")
                        ->orWhere('agencia_codigo', 'like', "%{$q}%");
                });
            }

            if ($agencia = trim((string) $request->query('agencia', ''))) {
                $query->where('agencia_codigo', CargotrackAgencia::normalizeCodigo($agencia));
            }

            if ($status = trim((string) $request->query('status', ''))) {
                if ($status === 'sin_asignar') {
                    $query->whereIn('assign_status', ['unmapped', 'pending'])
                        ->where(function ($b) {
                            $b->whereNull('cliente_id')->orWhereNull('inventario_id');
                        });
                } elseif ($status === 'en_inventario') {
                    $query->whereNotNull('inventario_id');
                } elseif ($status !== '') {
                    $query->where('assign_status', $status);
                }
            }

            if ($clienteId = $request->query('cliente_id')) {
                $query->where('cliente_id', $clienteId);
            }

            if ($modalidad = trim((string) $request->query('modalidad', ''))) {
                $query->where('modalidad', $modalidad);
            }

            if ($request->query('doc') === '1') {
                $query->whereNotNull('documento_path');
            } elseif ($request->query('doc') === '0') {
                $query->whereNull('documento_path');
            }

            $inbound = $query->paginate(25)->withQueryString();

            $agenciaCodigos = CargotrackInbound::query()
                ->whereNotNull('agencia_codigo')
                ->distinct()
                ->orderBy('agencia_codigo')
                ->pluck('agencia_codigo');
        }

        return view('cargotrack.admin', compact(
            'tab',
            'settings',
            'agencias',
            'clientes',
            'inbound',
            'stats',
            'agenciaCodigos'
        ));
    }

    protected function resolveTab(?string $tab): string
    {
        $allowed = ['resumen', 'inventario', 'agencias', 'config'];

        return in_array($tab, $allowed, true) ? $tab : 'resumen';
    }

    protected function redirectToTab(string $tab)
    {
        return redirect()->route('cargotrack.index', ['tab' => $tab]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'admin_username' => ['nullable', 'string', 'max:150'],
            'admin_password' => ['nullable', 'string', 'max:255'],
            'sync_enabled' => ['nullable', 'boolean'],
            'fetch_documents' => ['nullable', 'boolean'],
            'clear_password' => ['nullable', 'boolean'],
        ]);

        $settings = CargotrackSetting::current();
        $settings->admin_username = trim((string) ($data['admin_username'] ?? '')) ?: null;
        $settings->sync_enabled = $request->boolean('sync_enabled');
        $settings->fetch_documents = $request->boolean('fetch_documents');

        if ($request->boolean('clear_password')) {
            $settings->admin_password = null;
        } elseif (! empty($data['admin_password'])) {
            $settings->admin_password = $data['admin_password'];
        }

        $settings->save();

        return $this->redirectToTab('config')->with('success', 'Configuración CargoTrack guardada.');
    }

    public function storeAgencia(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:50'],
            'nombre' => ['nullable', 'string', 'max:150'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $codigo = CargotrackAgencia::normalizeCodigo($data['codigo']);

        CargotrackAgencia::updateOrCreate(
            ['codigo' => $codigo],
            [
                'nombre' => $data['nombre'] ?: $codigo,
                'cliente_id' => $data['cliente_id'] ?? null,
                'activo' => $request->boolean('activo', true),
            ]
        );

        return $this->redirectToTab('agencias')->with('success', "Agencia {$codigo} guardada.");
    }

    public function updateAgencia(Request $request, $id)
    {
        $agencia = CargotrackAgencia::findOrFail($id);
        $data = $request->validate([
            'nombre' => ['nullable', 'string', 'max:150'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $agencia->nombre = $data['nombre'] ?? $agencia->nombre;
        $agencia->cliente_id = $data['cliente_id'] ?? null;
        $agencia->activo = $request->boolean('activo');
        $agencia->save();

        return $this->redirectToTab('agencias')->with('success', 'Agencia actualizada.');
    }

    public function destroyAgencia($id)
    {
        CargotrackAgencia::findOrFail($id)->delete();

        return $this->redirectToTab('agencias')->with('success', 'Agencia eliminada.');
    }

    public function sync(TrackingSyncService $sync)
    {
        $stats = $sync->syncAdmin();

        $msg = sprintf(
            'Sync: %d listados, %d inbound, %d asignados, %d sin mapa, %d creados, %d actualizados, %d docs.',
            $stats['listed'],
            $stats['inbound'],
            $stats['assigned'],
            $stats['unmapped'],
            $stats['created'],
            $stats['updated'],
            $stats['documents']
        );

        $redirect = $this->redirectToTab('inventario')->with('success', $msg);

        if ($stats['errors'] !== []) {
            return $redirect->withErrors(['cargotrack' => implode(' ', array_slice($stats['errors'], 0, 5))]);
        }

        return $redirect;
    }

    public function assign(Request $request, $id, TrackingSyncService $sync)
    {
        $inbound = CargotrackInbound::findOrFail($id);
        $data = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
        ]);

        $cliente = Cliente::findOrFail($data['cliente_id']);
        $result = $sync->assignInboundToCliente($inbound, $cliente);

        if (! $result['ok']) {
            return $this->redirectToTab('inventario')
                ->withErrors(['cargotrack' => $result['error'] ?? 'No se pudo asignar el paquete.']);
        }

        $accion = $result['result'] === 'created' ? 'creado en inventario' : 'actualizado en inventario';

        return $this->redirectToTab('inventario')
            ->with('success', "Guía {$inbound->warehouse} asignada a {$cliente->nombre_completo} ({$accion}).");
    }

    public function assignBulk(Request $request, TrackingSyncService $sync)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:cargotrack_inbound,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
        ]);

        $cliente = Cliente::findOrFail($data['cliente_id']);
        $result = $sync->assignInboundBulk($data['ids'], $cliente);

        $msg = "Asignación masiva a {$cliente->nombre_completo}: {$result['ok']} ok, {$result['fail']} fallidos.";
        $redirect = $this->redirectToTab('inventario')->with('success', $msg);

        if ($result['errors'] !== []) {
            return $redirect->withErrors(['cargotrack' => implode(' ', array_slice($result['errors'], 0, 5))]);
        }

        return $redirect;
    }

    public function documento($id): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $inbound = CargotrackInbound::findOrFail($id);
        if (! $inbound->documento_path || ! Storage::disk('local')->exists($inbound->documento_path)) {
            return $this->redirectToTab('inventario')
                ->withErrors(['cargotrack' => 'Este paquete aún no tiene documento descargado.']);
        }

        return Storage::disk('local')->response(
            $inbound->documento_path,
            basename($inbound->documento_path),
            ['Content-Type' => $inbound->documento_mime ?: 'application/octet-stream']
        );
    }
}
