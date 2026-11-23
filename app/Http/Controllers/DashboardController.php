<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Facturacion;
use App\Models\Inventario;
use App\Models\User;
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

    /** Panel principal: totales, últimos paquetes y estadísticas por cliente. */
    public function index()
    {
        // Las cifras (dinero, libras, por cliente) son solo para administradores.
        if (! auth()->user()?->esAdmin()) {
            return view('welcome');
        }

        $totalClientes = Cliente::count();
        $totalUsuarios = User::count();
        $totalFacturas = Facturacion::count();
        $totalPaquetes = Inventario::count();
        $ultimosPaquetes = Inventario::with(['cliente', 'servicio'])
            ->latest('fecha_ingreso')
            ->take(5)
            ->get();

        // Solo lo que usa el buscador del dashboard (no exponer el resto de campos en el HTML).
        $clientes = Cliente::orderBy('nombre_completo')->get(['id', 'nombre_completo']);

        // Una sola consulta para todos los paquetes, agrupados por cliente en memoria.
        $porCliente = Inventario::with('servicio:id,tipo_servicio')
            ->get(['id', 'cliente_id', 'servicio_id', 'monto_calculado', 'peso_lb'])
            ->groupBy('cliente_id');

        $clientesData = [];
        foreach ($clientes as $cliente) {
            $g = $this->agregarPorTipoServicio($porCliente->get($cliente->id, collect()));
            $clientesData[$cliente->id] = [
                'paquetes_aereo' => $g['aereo']['paquetes'],
                'paquetes_maritimo' => $g['maritimo']['paquetes'],
                'paquetes_pie_cubico' => $g['pie_cubico']['paquetes'],
                'ingresos_aereo' => $g['aereo']['dinero'],
                'ingresos_maritimo' => $g['maritimo']['dinero'],
                'ingresos_pie_cubico' => $g['pie_cubico']['dinero'],
                'libras_aereo' => $g['aereo']['libras'],
                'libras_maritimo' => $g['maritimo']['libras'],
                'libras_pie_cubico' => $g['pie_cubico']['libras'],
            ];
        }

        // Gráfico de pastel: paquetes del mes agrupados por tipo de servicio
        $serviciosPieData = Inventario::with('servicio:id,tipo_servicio')
            ->whereBetween('fecha_ingreso', [
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ])
            ->get(['id', 'servicio_id'])
            ->groupBy(fn ($item) => $item->servicio->tipo_servicio ?? 'Sin tipo')
            ->map->count();

        // Resumen del mes actual vs. mes anterior (una consulta agregada por mes).
        $resumenMes = function (Carbon $inicio, Carbon $fin) {
            $r = Inventario::whereBetween('fecha_ingreso', [$inicio->toDateString(), $fin->toDateString()])
                ->selectRaw('COUNT(*) as paquetes, COALESCE(SUM(peso_lb),0) as libras, COALESCE(SUM(monto_calculado),0) as monto, COUNT(DISTINCT cliente_id) as clientes')
                ->first();

            return [
                'paquetes' => (int) $r->paquetes,
                'libras' => (float) $r->libras,
                'monto' => (float) $r->monto,
                'clientes' => (int) $r->clientes,
            ];
        };
        $mesActual = $resumenMes(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        $mesAnterior = $resumenMes(Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth());

        return view('welcome', compact(
            'totalClientes', 'totalUsuarios', 'totalFacturas', 'totalPaquetes',
            'ultimosPaquetes', 'clientes', 'clientesData', 'serviciosPieData',
            'mesActual', 'mesAnterior'
        ));
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
