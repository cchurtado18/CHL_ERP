<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ContaGasto;
use App\Models\ParametroRentabilidad;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContabilidadRentabilidadController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('es');

        [$desde, $hasta, $rangoLabel, $preset] = $this->resolverRango($request);

        // Mapa de costos por servicio (al final del período seleccionado)
        $costosMap = $this->construirMapaCostos($hasta);

        // Paquetería
        $rentaPaqueteria = $this->calcularRentabilidadGlobal($desde, $hasta, $costosMap);
        $clientes = $this->rentabilidadPorCliente($desde, $hasta, $costosMap);

        // Encomiendas familiares
        $rentaEncomiendas = $this->calcularRentabilidadEncomiendas($desde, $hasta, $costosMap);
        $remitentes = $this->rentabilidadPorRemitente($desde, $hasta, $costosMap);

        // Total combinado (paquetería + encomiendas) — usado para KPIs, run-rate, punto de equilibrio
        $rentabilidadActual = $this->combinarRentabilidad($rentaPaqueteria, $rentaEncomiendas);

        // Comparativo mes actual / mes anterior (combinados)
        $inicioMesActual = now()->startOfMonth();
        $finMesActual = now()->endOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        $costosMapMesActual = $this->construirMapaCostos($finMesActual);
        $costosMapMesAnterior = $this->construirMapaCostos($finMesAnterior);

        $rentaMesActual = $this->combinarRentabilidad(
            $this->calcularRentabilidadGlobal($inicioMesActual, $finMesActual, $costosMapMesActual),
            $this->calcularRentabilidadEncomiendas($inicioMesActual, $finMesActual, $costosMapMesActual)
        );
        $rentaMesAnterior = $this->combinarRentabilidad(
            $this->calcularRentabilidadGlobal($inicioMesAnterior, $finMesAnterior, $costosMapMesAnterior),
            $this->calcularRentabilidadEncomiendas($inicioMesAnterior, $finMesAnterior, $costosMapMesAnterior)
        );

        $variaciones = [
            'libras'         => $this->variacion($rentaMesActual['libras'], $rentaMesAnterior['libras']),
            'ingreso'        => $this->variacion($rentaMesActual['ingreso'], $rentaMesAnterior['ingreso']),
            'costo'          => $this->variacion($rentaMesActual['costo'], $rentaMesAnterior['costo'], inverso: true),
            'ganancia_bruta' => $this->variacion($rentaMesActual['ganancia_bruta'], $rentaMesAnterior['ganancia_bruta']),
            'gastos'         => $this->variacion($rentaMesActual['gastos'], $rentaMesAnterior['gastos'], inverso: true),
            'neto'           => $this->variacion($rentaMesActual['neto'], $rentaMesAnterior['neto']),
        ];

        $gastosPorCategoria = $this->gastosPorCategoria($desde, $hasta);
        $puntoEquilibrio = $this->calcularPuntoEquilibrio($rentabilidadActual);
        $runRate = $this->calcularRunRate($rentabilidadActual, $desde, $hasta);
        $clientesEnPerdida = collect($clientes)->where('estado', 'perdida')->count();
        $remitentesEnPerdida = collect($remitentes)->where('estado', 'perdida')->count();

        $labelMesActual = mb_convert_case($inicioMesActual->isoFormat('MMMM YYYY'), MB_CASE_TITLE, 'UTF-8');
        $labelMesAnterior = mb_convert_case($inicioMesAnterior->isoFormat('MMMM YYYY'), MB_CASE_TITLE, 'UTF-8');

        // Para mostrar en la cabecera el detalle de costos por servicio
        $serviciosConCosto = $this->resumenCostosServicios($hasta);

        return view('contabilidad.rentabilidad.index', compact(
            'desde', 'hasta', 'rangoLabel', 'preset',
            'serviciosConCosto',
            'rentabilidadActual',
            'rentaPaqueteria', 'rentaEncomiendas',
            'clientes', 'remitentes',
            'rentaMesActual', 'rentaMesAnterior', 'variaciones',
            'gastosPorCategoria',
            'puntoEquilibrio', 'runRate',
            'clientesEnPerdida', 'remitentesEnPerdida',
            'labelMesActual', 'labelMesAnterior'
        ));
    }

    public function cliente(Request $request, int $cliente)
    {
        Carbon::setLocale('es');

        $clienteModel = Cliente::query()->findOrFail($cliente);
        [$desde, $hasta, $rangoLabel, $preset] = $this->resolverRango($request);

        $costosMap = $this->construirMapaCostos($hasta);

        $paquetes = DB::table('inventario as i')
            ->join('facturacion as f', 'f.id', '=', 'i.factura_id')
            ->leftJoin('servicios as s', 's.id', '=', 'i.servicio_id')
            ->where('i.cliente_id', $cliente)
            ->where('f.tipo_factura', 'paqueteria')
            ->when(Schema::hasColumn('facturacion', 'anulada'), function ($q) {
                return $q->where(function ($qq) {
                    $qq->where('f.anulada', 0)->orWhereNull('f.anulada');
                });
            })
            ->whereBetween('f.fecha_factura', [$desde->toDateString(), $hasta->toDateString()])
            ->orderByDesc('f.fecha_factura')
            ->orderByDesc('i.id')
            ->select(
                'i.id',
                'i.peso_lb',
                'i.servicio_id',
                'i.tarifa_manual',
                'i.monto_calculado',
                'i.numero_guia',
                'i.tracking_codigo',
                'f.id as factura_id',
                'f.fecha_factura',
                'f.folio',
                's.tipo_servicio as servicio'
            )
            ->get()
            ->map(function ($p) use ($costosMap) {
                $peso = (float) $p->peso_lb;
                $ingreso = (float) $p->monto_calculado;
                $costoUnit = $this->resolverCosto($costosMap, $p->servicio_id);
                $costo = $peso * $costoUnit;
                $tarifaUsada = $peso > 0 ? $ingreso / $peso : 0;

                $p->peso = $peso;
                $p->ingreso = $ingreso;
                $p->costo = $costo;
                $p->costo_unit = $costoUnit;
                $p->ganancia = $ingreso - $costo;
                $p->margen = $ingreso > 0 ? (($ingreso - $costo) / $ingreso) * 100 : 0;
                $p->tarifa_usada = $tarifaUsada;

                return $p;
            });

        $totalLibras = $paquetes->sum('peso');
        $totalIngreso = $paquetes->sum('ingreso');
        $totalCosto = $paquetes->sum('costo');
        $totalGanancia = $totalIngreso - $totalCosto;
        $margen = $totalIngreso > 0 ? ($totalGanancia / $totalIngreso) * 100 : 0;
        $tarifaPromedio = $totalLibras > 0 ? $totalIngreso / $totalLibras : 0;
        $costoPromedio = $totalLibras > 0 ? $totalCosto / $totalLibras : 0;
        $estado = $this->evaluarEstadoCliente($tarifaPromedio, $costoPromedio, $margen);

        $historico = $this->historicoClienteUltimosMeses($cliente, 6);

        return view('contabilidad.rentabilidad.cliente', [
            'cliente' => $clienteModel,
            'desde' => $desde,
            'hasta' => $hasta,
            'rangoLabel' => $rangoLabel,
            'preset' => $preset,
            'paquetes' => $paquetes,
            'costoPromedio' => $costoPromedio,
            'totalLibras' => $totalLibras,
            'totalIngreso' => $totalIngreso,
            'totalCosto' => $totalCosto,
            'totalGanancia' => $totalGanancia,
            'margen' => $margen,
            'tarifaPromedio' => $tarifaPromedio,
            'estado' => $estado,
            'historico' => $historico,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Construye un mapa [servicio_id => costo_por_libra] vigente a la fecha dada.
     * Incluye la clave "null" como fallback global.
     */
    private function construirMapaCostos(Carbon $fechaRef): array
    {
        $global = (float) (ParametroRentabilidad::vigentePorServicio(null, $fechaRef)?->costo_fijo_por_libra ?? 0);
        $map = ['_global' => $global];

        $servicios = Servicio::query()->get(['id']);
        foreach ($servicios as $s) {
            $costo = ParametroRentabilidad::vigentePorServicio($s->id, $fechaRef);
            $map[$s->id] = $costo ? (float) $costo->costo_fijo_por_libra : $global;
        }

        return $map;
    }

    private function resolverCosto(array $costosMap, ?int $servicioId): float
    {
        if ($servicioId !== null && isset($costosMap[$servicioId])) {
            return (float) $costosMap[$servicioId];
        }
        return (float) ($costosMap['_global'] ?? 0);
    }

    /**
     * Devuelve la lista de servicios con su costo vigente actual, para mostrar en UI.
     */
    private function resumenCostosServicios(Carbon $fechaRef): array
    {
        $resumen = [];
        $servicios = Servicio::query()->orderBy('tipo_servicio')->get(['id', 'tipo_servicio']);
        $global = ParametroRentabilidad::vigentePorServicio(null, $fechaRef);

        foreach ($servicios as $s) {
            $especifico = ParametroRentabilidad::vigentePorServicio($s->id, $fechaRef);
            $tieneEspecifico = $especifico && $especifico->servicio_id == $s->id;
            $costo = $tieneEspecifico ? (float) $especifico->costo_fijo_por_libra : (float) ($global?->costo_fijo_por_libra ?? 0);

            $resumen[] = [
                'id' => $s->id,
                'nombre' => $s->tipo_servicio,
                'costo' => $costo,
                'es_especifico' => $tieneEspecifico,
            ];
        }

        return $resumen;
    }

    private function resolverRango(Request $request): array
    {
        $preset = (string) $request->query('preset', 'mes_actual');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        if ($desde && $hasta) {
            $d = Carbon::parse($desde)->startOfDay();
            $h = Carbon::parse($hasta)->endOfDay();
            return [$d, $h, 'Rango personalizado: '.$d->format('d/m/Y').' – '.$h->format('d/m/Y'), 'custom'];
        }

        $cap = fn ($s) => mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');

        return match ($preset) {
            'mes_anterior' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth(), 'Mes anterior ('.$cap(now()->subMonthNoOverflow()->isoFormat('MMMM YYYY')).')', $preset],
            'trimestre' => [now()->startOfQuarter(), now()->endOfQuarter(), 'Trimestre actual', $preset],
            'anio' => [now()->startOfYear(), now()->endOfYear(), 'Año '.now()->year, $preset],
            'ultimos_30' => [now()->subDays(30)->startOfDay(), now()->endOfDay(), 'Últimos 30 días', $preset],
            default => [now()->startOfMonth(), now()->endOfMonth(), 'Mes actual ('.$cap(now()->isoFormat('MMMM YYYY')).')', 'mes_actual'],
        };
    }

    /**
     * Suma global de libras/ingreso/costo del período usando costo por servicio.
     */
    private function calcularRentabilidadGlobal(Carbon $desde, Carbon $hasta, array $costosMap): array
    {
        $rows = DB::table('inventario as i')
            ->join('facturacion as f', 'f.id', '=', 'i.factura_id')
            ->where('f.tipo_factura', 'paqueteria')
            ->when(Schema::hasColumn('facturacion', 'anulada'), function ($q) {
                return $q->where(function ($qq) {
                    $qq->where('f.anulada', 0)->orWhereNull('f.anulada');
                });
            })
            ->whereBetween('f.fecha_factura', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('i.servicio_id, COALESCE(SUM(i.peso_lb), 0) AS libras, COALESCE(SUM(i.monto_calculado), 0) AS ingreso')
            ->groupBy('i.servicio_id')
            ->get();

        $libras = 0.0;
        $ingreso = 0.0;
        $costo = 0.0;
        foreach ($rows as $r) {
            $libras += (float) $r->libras;
            $ingreso += (float) $r->ingreso;
            $costo += ((float) $r->libras) * $this->resolverCosto($costosMap, $r->servicio_id);
        }

        $gananciaBruta = $ingreso - $costo;

        $gastos = (float) ContaGasto::query()
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->sum('monto');

        $neto = $gananciaBruta - $gastos;
        $margenNeto = $ingreso > 0 ? ($neto / $ingreso) * 100 : 0;
        $tarifaPromedio = $libras > 0 ? $ingreso / $libras : 0;
        $costoPromedio = $libras > 0 ? $costo / $libras : 0;

        return [
            'libras' => $libras,
            'ingreso' => $ingreso,
            'costo' => $costo,
            'ganancia_bruta' => $gananciaBruta,
            'gastos' => $gastos,
            'neto' => $neto,
            'margen_neto' => $margenNeto,
            'tarifa_promedio' => $tarifaPromedio,
            'costo_promedio' => $costoPromedio,
        ];
    }

    private function rentabilidadPorCliente(Carbon $desde, Carbon $hasta, array $costosMap): array
    {
        $rows = DB::table('inventario as i')
            ->join('facturacion as f', 'f.id', '=', 'i.factura_id')
            ->join('clientes as c', 'c.id', '=', 'i.cliente_id')
            ->leftJoin('servicios as s', 's.id', '=', 'i.servicio_id')
            ->where('f.tipo_factura', 'paqueteria')
            ->when(Schema::hasColumn('facturacion', 'anulada'), function ($q) {
                return $q->where(function ($qq) {
                    $qq->where('f.anulada', 0)->orWhereNull('f.anulada');
                });
            })
            ->whereBetween('f.fecha_factura', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('c.id, c.nombre_completo, i.servicio_id, s.tipo_servicio AS servicio_nombre, COALESCE(SUM(i.peso_lb), 0) AS libras, COALESCE(SUM(i.monto_calculado), 0) AS ingreso, COUNT(i.id) AS paquetes')
            ->groupBy('c.id', 'c.nombre_completo', 'i.servicio_id', 's.tipo_servicio')
            ->get();

        // Reagrupar por cliente, guardando también desglose por servicio
        $byCliente = [];
        foreach ($rows as $r) {
            $key = $r->id;
            if (! isset($byCliente[$key])) {
                $byCliente[$key] = [
                    'id' => $r->id,
                    'nombre' => $r->nombre_completo,
                    'libras' => 0.0,
                    'paquetes' => 0,
                    'ingreso' => 0.0,
                    'costo' => 0.0,
                    'por_servicio' => [],
                ];
            }
            $libras = (float) $r->libras;
            $ingreso = (float) $r->ingreso;
            $costoUnit = $this->resolverCosto($costosMap, $r->servicio_id);
            $costoServ = $libras * $costoUnit;

            $byCliente[$key]['libras'] += $libras;
            $byCliente[$key]['paquetes'] += (int) $r->paquetes;
            $byCliente[$key]['ingreso'] += $ingreso;
            $byCliente[$key]['costo'] += $costoServ;
            $byCliente[$key]['por_servicio'][] = [
                'servicio_id' => $r->servicio_id,
                'nombre' => $r->servicio_nombre ?? 'Sin servicio',
                'libras' => $libras,
                'paquetes' => (int) $r->paquetes,
                'ingreso' => $ingreso,
                'costo_unit' => $costoUnit,
                'costo' => $costoServ,
                'tarifa' => $libras > 0 ? $ingreso / $libras : 0,
                'ganancia' => $ingreso - $costoServ,
                'margen' => $ingreso > 0 ? (($ingreso - $costoServ) / $ingreso) * 100 : 0,
            ];
        }

        $resultado = [];
        foreach ($byCliente as $c) {
            $ganancia = $c['ingreso'] - $c['costo'];
            $margen = $c['ingreso'] > 0 ? ($ganancia / $c['ingreso']) * 100 : 0;
            $tarifa = $c['libras'] > 0 ? $c['ingreso'] / $c['libras'] : 0;
            $costoProm = $c['libras'] > 0 ? $c['costo'] / $c['libras'] : 0;

            $resultado[] = [
                'id' => $c['id'],
                'nombre' => $c['nombre'],
                'libras' => $c['libras'],
                'paquetes' => $c['paquetes'],
                'tarifa_promedio' => $tarifa,
                'costo_promedio' => $costoProm,
                'ingreso' => $c['ingreso'],
                'costo' => $c['costo'],
                'ganancia' => $ganancia,
                'margen' => $margen,
                'estado' => $this->evaluarEstadoCliente($tarifa, $costoProm, $margen),
                'por_servicio' => $c['por_servicio'],
            ];
        }

        usort($resultado, fn ($a, $b) => $b['ganancia'] <=> $a['ganancia']);

        return $resultado;
    }

    private function evaluarEstadoCliente(float $tarifaPromedio, float $costoPromedio, float $margen): string
    {
        if ($costoPromedio > 0 && $tarifaPromedio > 0 && $tarifaPromedio <= $costoPromedio) {
            return 'perdida';
        }
        if ($margen < 15) {
            return 'bajo';
        }
        return 'saludable';
    }

    private function gastosPorCategoria(Carbon $desde, Carbon $hasta)
    {
        return DB::table('conta_gastos as g')
            ->join('conta_gasto_categorias as cat', 'cat.id', '=', 'g.categoria_id')
            ->whereNull('g.deleted_at')
            ->whereBetween('g.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('cat.id, cat.nombre, cat.icono, SUM(g.monto) AS total, COUNT(g.id) AS cantidad')
            ->groupBy('cat.id', 'cat.nombre', 'cat.icono')
            ->orderByDesc('total')
            ->get();
    }

    private function variacion(float $actual, float $anterior, bool $inverso = false): array
    {
        $direccion = 'flat';
        $pct = null;

        if ($anterior > 0) {
            $pct = (($actual - $anterior) / $anterior) * 100;
            $direccion = $pct > 0.5 ? 'up' : ($pct < -0.5 ? 'down' : 'flat');
        } elseif ($actual > 0) {
            $direccion = 'up';
        }

        return [
            'pct' => $pct,
            'direccion' => $direccion,
            'inverso' => $inverso,
        ];
    }

    private function calcularPuntoEquilibrio(array $renta): array
    {
        $margenPromedioLb = $renta['libras'] > 0 ? ($renta['ingreso'] - $renta['costo']) / $renta['libras'] : 0;
        $faltante = max(0, $renta['gastos'] - $renta['ganancia_bruta']);

        $librasNecesarias = $margenPromedioLb > 0 ? $faltante / $margenPromedioLb : 0;
        $superado = $renta['ganancia_bruta'] >= $renta['gastos'];

        return [
            'superado' => $superado,
            'libras_necesarias' => $librasNecesarias,
            'libras_actuales' => $renta['libras'],
            'gastos' => $renta['gastos'],
            'falta_cubrir' => $faltante,
            'margen_promedio_lb' => $margenPromedioLb,
        ];
    }

    private function calcularRunRate(array $rentaActual, Carbon $desde, Carbon $hasta): ?array
    {
        $hoy = now();
        $esMesActualEnCurso = $desde->isSameMonth($hoy) && $hasta->isSameMonth($hoy) && $hasta->greaterThanOrEqualTo($hoy);

        if (! $esMesActualEnCurso) {
            return null;
        }

        $diasTranscurridos = max(1, $hoy->day);
        $diasTotalesMes = $hoy->daysInMonth;
        $factor = $diasTotalesMes / $diasTranscurridos;

        return [
            'dia_actual' => $diasTranscurridos,
            'dias_totales' => $diasTotalesMes,
            'porcentaje_mes' => round(($diasTranscurridos / $diasTotalesMes) * 100, 1),
            'libras_proyectadas' => $rentaActual['libras'] * $factor,
            'ingreso_proyectado' => $rentaActual['ingreso'] * $factor,
            'ganancia_bruta_proyectada' => $rentaActual['ganancia_bruta'] * $factor,
            'neto_proyectado' => ($rentaActual['ganancia_bruta'] * $factor) - $rentaActual['gastos'],
        ];
    }

    /**
     * Mapa { 'nombre_normalizado' => servicio_id } para mapear strings tipo
     * 'maritimo' o 'aereo' (de encomiendas.tipo_servicio) al servicio_id real.
     */
    private function mapaServiciosPorNombre(): array
    {
        $servicios = Servicio::query()->get(['id', 'tipo_servicio']);
        $map = [];
        foreach ($servicios as $s) {
            $map[$this->normalizarTexto($s->tipo_servicio)] = $s->id;
        }
        return $map;
    }

    private function normalizarTexto(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = strtr($s, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ñ' => 'n',
        ]);
        return trim($s);
    }

    /**
     * Combina los totales de paquetería y encomiendas en un solo resumen
     * para los KPIs/comparativos.
     */
    private function combinarRentabilidad(array $paqueteria, array $encomiendas): array
    {
        $libras = (float) $paqueteria['libras'] + (float) ($encomiendas['libras'] ?? 0);
        $ingreso = (float) $paqueteria['ingreso'] + (float) ($encomiendas['ingreso'] ?? 0);
        $costo = (float) $paqueteria['costo'] + (float) ($encomiendas['costo'] ?? 0);
        $gananciaBruta = $ingreso - $costo;

        // Los gastos extras ya están calculados en paquetería (son globales)
        $gastos = (float) $paqueteria['gastos'];
        $neto = $gananciaBruta - $gastos;
        $margenNeto = $ingreso > 0 ? ($neto / $ingreso) * 100 : 0;

        return [
            'libras' => $libras,
            'pies3' => (float) ($encomiendas['pies3'] ?? 0),
            'ingreso' => $ingreso,
            'costo' => $costo,
            'ganancia_bruta' => $gananciaBruta,
            'gastos' => $gastos,
            'neto' => $neto,
            'margen_neto' => $margenNeto,
            'tarifa_promedio' => $libras > 0 ? $ingreso / $libras : 0,
            'costo_promedio' => $libras > 0 ? $costo / $libras : 0,
        ];
    }

    /**
     * Calcula libras/pies³/ingreso/costo totales de las encomiendas familiares
     * en el período usando el costo del servicio correspondiente.
     */
    private function calcularRentabilidadEncomiendas(Carbon $desde, Carbon $hasta, array $costosMap): array
    {
        if (! Schema::hasTable('encomienda_items') || ! Schema::hasTable('encomiendas')) {
            return ['libras' => 0, 'pies3' => 0, 'items' => 0, 'ingreso' => 0, 'costo' => 0, 'ganancia' => 0];
        }

        $mapaServicios = $this->mapaServiciosPorNombre();
        $idPieCubico = $mapaServicios['pie cubico'] ?? null;

        $rows = DB::table('encomienda_items as ei')
            ->join('encomiendas as e', 'e.id', '=', 'ei.encomienda_id')
            ->join('facturacion as f', 'f.encomienda_id', '=', 'e.id')
            ->where('f.tipo_factura', 'encomienda_familiar')
            ->when(Schema::hasColumn('facturacion', 'anulada'), function ($q) {
                return $q->where(function ($qq) {
                    $qq->where('f.anulada', 0)->orWhereNull('f.anulada');
                });
            })
            ->whereBetween('f.fecha_factura', [$desde->toDateString(), $hasta->toDateString()])
            ->select(
                'ei.metodo_cobro',
                'ei.peso_lb',
                'ei.pie_cubico',
                'ei.monto_total_item',
                'e.tipo_servicio'
            )
            ->get();

        $libras = 0.0;
        $pies3 = 0.0;
        $items = 0;
        $ingreso = 0.0;
        $costo = 0.0;

        foreach ($rows as $r) {
            $items++;
            $ingreso += (float) $r->monto_total_item;

            if ($r->metodo_cobro === 'pie_cubico') {
                $units = (float) $r->pie_cubico;
                $pies3 += $units;
                $costo += $units * $this->resolverCosto($costosMap, $idPieCubico);
            } elseif ($r->metodo_cobro === 'peso') {
                $units = (float) $r->peso_lb;
                $libras += $units;
                $idServ = $mapaServicios[$this->normalizarTexto((string) $r->tipo_servicio)] ?? null;
                $costo += $units * $this->resolverCosto($costosMap, $idServ);
            }
        }

        return [
            'libras' => $libras,
            'pies3' => $pies3,
            'items' => $items,
            'ingreso' => $ingreso,
            'costo' => $costo,
            'ganancia' => $ingreso - $costo,
        ];
    }

    /**
     * Rentabilidad agrupada por remitente (encomiendas familiares).
     */
    private function rentabilidadPorRemitente(Carbon $desde, Carbon $hasta, array $costosMap): array
    {
        if (! Schema::hasTable('encomienda_items') || ! Schema::hasTable('encomiendas') || ! Schema::hasTable('remitentes')) {
            return [];
        }

        $mapaServicios = $this->mapaServiciosPorNombre();
        $idPieCubico = $mapaServicios['pie cubico'] ?? null;

        $rows = DB::table('encomienda_items as ei')
            ->join('encomiendas as e', 'e.id', '=', 'ei.encomienda_id')
            ->join('facturacion as f', 'f.encomienda_id', '=', 'e.id')
            ->join('remitentes as r', 'r.id', '=', 'e.remitente_id')
            ->where('f.tipo_factura', 'encomienda_familiar')
            ->when(Schema::hasColumn('facturacion', 'anulada'), function ($q) {
                return $q->where(function ($qq) {
                    $qq->where('f.anulada', 0)->orWhereNull('f.anulada');
                });
            })
            ->whereBetween('f.fecha_factura', [$desde->toDateString(), $hasta->toDateString()])
            ->select(
                'r.id as remitente_id',
                'r.nombre_completo',
                'ei.metodo_cobro',
                'ei.peso_lb',
                'ei.pie_cubico',
                'ei.monto_total_item',
                'e.tipo_servicio'
            )
            ->get();

        $byRemitente = [];
        foreach ($rows as $r) {
            $key = $r->remitente_id;
            if (! isset($byRemitente[$key])) {
                $byRemitente[$key] = [
                    'id' => $r->remitente_id,
                    'nombre' => $r->nombre_completo,
                    'libras' => 0.0,
                    'pies3' => 0.0,
                    'items' => 0,
                    'ingreso' => 0.0,
                    'costo' => 0.0,
                    'por_metodo' => [],
                ];
            }

            $byRemitente[$key]['items']++;
            $ingreso = (float) $r->monto_total_item;
            $byRemitente[$key]['ingreso'] += $ingreso;

            if ($r->metodo_cobro === 'pie_cubico') {
                $units = (float) $r->pie_cubico;
                $costoUnit = $this->resolverCosto($costosMap, $idPieCubico);
                $costoItem = $units * $costoUnit;
                $byRemitente[$key]['pies3'] += $units;
                $byRemitente[$key]['costo'] += $costoItem;

                $byRemitente[$key]['por_metodo']['pie_cubico'] = ($byRemitente[$key]['por_metodo']['pie_cubico'] ?? [
                    'etiqueta' => 'Pie cúbico',
                    'unidad' => 'pie³',
                    'cantidad' => 0,
                    'ingreso' => 0,
                    'costo' => 0,
                    'costo_unit' => $costoUnit,
                ]);
                $byRemitente[$key]['por_metodo']['pie_cubico']['cantidad'] += $units;
                $byRemitente[$key]['por_metodo']['pie_cubico']['ingreso'] += $ingreso;
                $byRemitente[$key]['por_metodo']['pie_cubico']['costo'] += $costoItem;
                $byRemitente[$key]['por_metodo']['pie_cubico']['costo_unit'] = $costoUnit;
            } elseif ($r->metodo_cobro === 'peso') {
                $units = (float) $r->peso_lb;
                $idServ = $mapaServicios[$this->normalizarTexto((string) $r->tipo_servicio)] ?? null;
                $costoUnit = $this->resolverCosto($costosMap, $idServ);
                $costoItem = $units * $costoUnit;
                $byRemitente[$key]['libras'] += $units;
                $byRemitente[$key]['costo'] += $costoItem;

                $clave = 'peso_'.($r->tipo_servicio ?? 'na');
                $byRemitente[$key]['por_metodo'][$clave] = ($byRemitente[$key]['por_metodo'][$clave] ?? [
                    'etiqueta' => 'Peso ('.ucfirst((string) $r->tipo_servicio).')',
                    'unidad' => 'lb',
                    'cantidad' => 0,
                    'ingreso' => 0,
                    'costo' => 0,
                    'costo_unit' => $costoUnit,
                ]);
                $byRemitente[$key]['por_metodo'][$clave]['cantidad'] += $units;
                $byRemitente[$key]['por_metodo'][$clave]['ingreso'] += $ingreso;
                $byRemitente[$key]['por_metodo'][$clave]['costo'] += $costoItem;
                $byRemitente[$key]['por_metodo'][$clave]['costo_unit'] = $costoUnit;
            }
        }

        $result = [];
        foreach ($byRemitente as $rem) {
            $ganancia = $rem['ingreso'] - $rem['costo'];
            $margen = $rem['ingreso'] > 0 ? ($ganancia / $rem['ingreso']) * 100 : 0;
            $rem['ganancia'] = $ganancia;
            $rem['margen'] = $margen;
            $rem['estado'] = $ganancia < 0 ? 'perdida' : ($margen < 15 ? 'bajo' : 'saludable');
            $rem['por_metodo'] = array_values($rem['por_metodo']);
            $result[] = $rem;
        }

        usort($result, fn ($a, $b) => $b['ganancia'] <=> $a['ganancia']);

        return $result;
    }

    private function historicoClienteUltimosMeses(int $clienteId, int $n): array
    {
        $serie = [];
        $cursor = now()->startOfMonth()->subMonths($n - 1);

        for ($i = 0; $i < $n; $i++) {
            $ini = $cursor->copy()->startOfMonth();
            $fin = $cursor->copy()->endOfMonth();

            $costosMap = $this->construirMapaCostos($fin);

            $rows = DB::table('inventario as i')
                ->join('facturacion as f', 'f.id', '=', 'i.factura_id')
                ->where('i.cliente_id', $clienteId)
                ->where('f.tipo_factura', 'paqueteria')
                ->when(Schema::hasColumn('facturacion', 'anulada'), function ($q) {
                    return $q->where(function ($qq) {
                        $qq->where('f.anulada', 0)->orWhereNull('f.anulada');
                    });
                })
                ->whereBetween('f.fecha_factura', [$ini->toDateString(), $fin->toDateString()])
                ->selectRaw('i.servicio_id, COALESCE(SUM(i.peso_lb), 0) AS libras, COALESCE(SUM(i.monto_calculado), 0) AS ingreso')
                ->groupBy('i.servicio_id')
                ->get();

            $libras = 0.0;
            $ingreso = 0.0;
            $costo = 0.0;
            foreach ($rows as $r) {
                $libras += (float) $r->libras;
                $ingreso += (float) $r->ingreso;
                $costo += ((float) $r->libras) * $this->resolverCosto($costosMap, $r->servicio_id);
            }

            $ganancia = $ingreso - $costo;
            $margen = $ingreso > 0 ? ($ganancia / $ingreso) * 100 : 0;

            $serie[] = [
                'label' => mb_convert_case($ini->isoFormat('MMM YY'), MB_CASE_TITLE, 'UTF-8'),
                'libras' => round($libras, 2),
                'ingreso' => round($ingreso, 2),
                'costo' => round($costo, 2),
                'ganancia' => round($ganancia, 2),
                'margen' => round($margen, 2),
            ];

            $cursor->addMonth();
        }

        return $serie;
    }
}
