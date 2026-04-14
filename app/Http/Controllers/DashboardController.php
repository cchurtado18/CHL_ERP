<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private function normalizarTipoServicio(?string $tipo): string
    {
        $t = strtolower($tipo ?? '');

        return str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $t);
    }

    /** @param  Collection<int, Inventario>  $paquetes */
    private function agregarPorTipoServicio(Collection $paquetes): array
    {
        $grupos = [
            'maritimo' => ['paquetes' => 0, 'dinero' => 0.0, 'libras' => 0.0],
            'aereo' => ['paquetes' => 0, 'dinero' => 0.0, 'libras' => 0.0],
            'pie_cubico' => ['paquetes' => 0, 'dinero' => 0.0, 'libras' => 0.0],
        ];

        foreach ($paquetes as $p) {
            $key = $this->normalizarTipoServicio($p->servicio->tipo_servicio ?? '');
            if (! isset($grupos[$key])) {
                continue;
            }
            $grupos[$key]['paquetes']++;
            $grupos[$key]['dinero'] += (float) ($p->monto_calculado ?? 0);
            $grupos[$key]['libras'] += (float) ($p->peso_lb ?? 0);
        }

        return $grupos;
    }

    public function estadisticasPaquetes(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $query = Inventario::with('servicio');
        if ($desde && $hasta) {
            $query->whereBetween('fecha_ingreso', [$desde, $hasta]);
        }
        $paquetes = $query->get();
        $maritimo = $paquetes->filter(function ($p) {
            return strtolower($p->servicio->tipo_servicio ?? '') === 'maritimo';
        });
        $aereo = $paquetes->filter(function ($p) {
            return strtolower($p->servicio->tipo_servicio ?? '') === 'aereo';
        });

        return response()->json([
            'maritimo' => [
                'cantidad' => $maritimo->count(),
                'libras' => $maritimo->sum('peso_lb'),
                'dinero' => $maritimo->sum('monto_calculado'),
            ],
            'aereo' => [
                'dinero' => $aereo->sum('monto_calculado'),
            ],
            'total_paquetes' => $paquetes->count(),
        ]);
    }

    public function estadisticasPaquetesCliente(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $tipoServicio = $request->input('tipo_servicio', 'todos');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $todosClientes = $clienteId === 'todos' || $clienteId === 'all';

        if ($todosClientes) {
            if (! $desde || ! $hasta) {
                $desde = Carbon::now()->startOfMonth()->toDateString();
                $hasta = Carbon::now()->endOfMonth()->toDateString();
            }

            $paquetesMesCompleto = Inventario::with('servicio')
                ->whereBetween('fecha_ingreso', [$desde, $hasta])
                ->get();

            $porTipo = $this->agregarPorTipoServicio($paquetesMesCompleto);

            if ($tipoServicio === 'todos') {
                $paquetesResumen = $paquetesMesCompleto;
            } else {
                $tipoNorm = $this->normalizarTipoServicio($tipoServicio);
                $paquetesResumen = $paquetesMesCompleto->filter(function ($p) use ($tipoNorm) {
                    return $this->normalizarTipoServicio($p->servicio->tipo_servicio ?? '') === $tipoNorm;
                });
            }

            return response()->json([
                'todos_clientes' => true,
                'desde' => $desde,
                'hasta' => $hasta,
                'total' => $paquetesResumen->count(),
                'dinero' => (float) $paquetesResumen->sum('monto_calculado'),
                'libras' => (float) $paquetesResumen->sum('peso_lb'),
                'por_tipo' => $porTipo,
            ]);
        }

        \Log::info('Filtro dashboard', [
            'cliente_id' => $clienteId,
            'tipo_servicio' => $tipoServicio,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);

        if (! $clienteId) {
            return response()->json([
                'total' => 0,
                'dinero' => 0,
                'libras' => 0,
                'todos_clientes' => false,
            ]);
        }

        $query = Inventario::with('servicio')
            ->where('cliente_id', $clienteId);
        if ($desde && $hasta) {
            $query->whereBetween('fecha_ingreso', [$desde, $hasta]);
        }
        if ($tipoServicio !== 'todos') {
            $tipoServicioNormalizado = $this->normalizarTipoServicio($tipoServicio);
            $query->whereHas('servicio', function ($q) use ($tipoServicioNormalizado) {
                $q->whereRaw(
                    "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(tipo_servicio, 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u')) = ?",
                    [$tipoServicioNormalizado]
                );
            });
        }
        $paquetes = $query->get();
        $total = $paquetes->count();
        $dinero = $paquetes->sum('monto_calculado');
        $libras = $paquetes->sum('peso_lb');

        return response()->json([
            'todos_clientes' => false,
            'total' => $total,
            'dinero' => $dinero,
            'libras' => $libras,
        ]);
    }
}
