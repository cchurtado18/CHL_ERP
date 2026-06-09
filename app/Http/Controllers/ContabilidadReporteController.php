<?php

namespace App\Http\Controllers;

use App\Models\ContaCobro;
use App\Models\ContaCxc;
use App\Models\Facturacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContabilidadReporteController extends Controller
{
    public function index(Request $request)
    {
        // Forzar español para los nombres de meses en isoFormat
        Carbon::setLocale('es');

        $setupPendiente = ! Schema::hasTable('conta_cobros') || ! Schema::hasTable('conta_cxc');

        // ─────────── RANGO ANALIZADO (preset o custom) ───────────
        [$desde, $hasta, $rangoLabel, $preset] = $this->resolverRango($request);

        // Mes actual / Mes anterior (fijos, para el comparativo principal)
        $inicioMesActual = now()->startOfMonth();
        $finMesActual = now()->endOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        // ─────────── KPIs comparativos ───────────
        $facturadoActual = $this->sumFacturado($inicioMesActual, $finMesActual);
        $facturadoAnterior = $this->sumFacturado($inicioMesAnterior, $finMesAnterior);
        $cantActual = $this->countFacturadas($inicioMesActual, $finMesActual);
        $cantAnterior = $this->countFacturadas($inicioMesAnterior, $finMesAnterior);

        $cobradoActual = 0.0;
        $cobradoAnterior = 0.0;
        $saldoCxcHoy = 0.0;
        $saldoCxcMesAnt = 0.0;
        $cobrosPorCuenta = collect();
        $totalCobradoRango = 0.0;
        $serieMeses = $this->serieUltimosMeses(6, ! $setupPendiente);

        if (! $setupPendiente) {
            $cobradoActual = $this->sumCobrado($inicioMesActual, $finMesActual);
            $cobradoAnterior = $this->sumCobrado($inicioMesAnterior, $finMesAnterior);
            $totalCobradoRango = $this->sumCobrado($desde, $hasta);

            $saldoCxcHoy = (float) ContaCxc::where('saldo_actual', '>', 0)->sum('saldo_actual');

            // Snapshot histórico al cierre del mes anterior
            $emitidoHastaMesAnt = (float) ContaCxc::where('fecha_emision', '<=', $finMesAnterior->toDateString())->sum('monto_original');
            $cobradoHastaMesAnt = (float) ContaCobro::where('fecha_pago', '<=', $finMesAnterior->toDateString())->sum('monto');
            $saldoCxcMesAnt = max(0, $emitidoHastaMesAnt - $cobradoHastaMesAnt);

            // Cobros agrupados por cuenta dentro del rango analizado
            $cobrosPorCuenta = ContaCobro::query()
                ->join('conta_cuentas', 'conta_cuentas.id', '=', 'conta_cobros.cuenta_banco_caja_id')
                ->whereBetween('conta_cobros.fecha_pago', [$desde->toDateString(), $hasta->toDateString()])
                ->select(
                    'conta_cuentas.id',
                    'conta_cuentas.codigo',
                    'conta_cuentas.nombre',
                    'conta_cuentas.subtipo',
                    DB::raw('SUM(conta_cobros.monto) AS total'),
                    DB::raw('COUNT(conta_cobros.id) AS movimientos')
                )
                ->groupBy('conta_cuentas.id', 'conta_cuentas.codigo', 'conta_cuentas.nombre', 'conta_cuentas.subtipo')
                ->orderByDesc('total')
                ->get();
        }

        $ticketActual = $cantActual > 0 ? $facturadoActual / $cantActual : 0.0;
        $ticketAnterior = $cantAnterior > 0 ? $facturadoAnterior / $cantAnterior : 0.0;

        $variaciones = [
            'facturado' => $this->variacion($facturadoActual, $facturadoAnterior),
            'cobrado' => $this->variacion($cobradoActual, $cobradoAnterior),
            'cxc' => $this->variacion($saldoCxcHoy, $saldoCxcMesAnt),
            'ticket' => $this->variacion($ticketActual, $ticketAnterior),
        ];

        // ─────────── Top clientes facturados en el rango ───────────
        $topClientes = $this->topClientesFacturados($desde, $hasta);
        $facturadoRango = $this->sumFacturado($desde, $hasta);

        // Labels legibles de los dos meses comparados (capitalizados)
        $labelMesActual = mb_convert_case($inicioMesActual->isoFormat('MMMM YYYY'), MB_CASE_TITLE, 'UTF-8');
        $labelMesAnterior = mb_convert_case($inicioMesAnterior->isoFormat('MMMM YYYY'), MB_CASE_TITLE, 'UTF-8');

        return view('contabilidad.reporte-ejecutivo', compact(
            'setupPendiente',
            'desde',
            'hasta',
            'rangoLabel',
            'preset',
            'facturadoActual',
            'facturadoAnterior',
            'cobradoActual',
            'cobradoAnterior',
            'saldoCxcHoy',
            'saldoCxcMesAnt',
            'ticketActual',
            'ticketAnterior',
            'cantActual',
            'cantAnterior',
            'variaciones',
            'serieMeses',
            'cobrosPorCuenta',
            'totalCobradoRango',
            'topClientes',
            'facturadoRango',
            'labelMesActual',
            'labelMesAnterior'
        ));
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

        $capitalize = fn ($s) => mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');

        return match ($preset) {
            'mes_anterior' => [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
                'Mes anterior ('.$capitalize(now()->subMonthNoOverflow()->isoFormat('MMMM YYYY')).')',
                $preset,
            ],
            'trimestre' => [now()->startOfQuarter(), now()->endOfQuarter(), 'Trimestre actual', $preset],
            'anio' => [now()->startOfYear(), now()->endOfYear(), 'Año '.now()->year, $preset],
            'ultimos_30' => [now()->subDays(30)->startOfDay(), now()->endOfDay(), 'Últimos 30 días', $preset],
            default => [now()->startOfMonth(), now()->endOfMonth(), 'Mes actual ('.$capitalize(now()->isoFormat('MMMM YYYY')).')', 'mes_actual'],
        };
    }

    private function sumFacturado(Carbon $desde, Carbon $hasta): float
    {
        return (float) Facturacion::query()
            ->when(Schema::hasColumn('facturacion', 'anulada'), fn ($q) => $q->noAnulada())
            ->whereBetween('fecha_factura', [$desde->toDateString(), $hasta->toDateString()])
            ->sum('monto_total');
    }

    private function countFacturadas(Carbon $desde, Carbon $hasta): int
    {
        return (int) Facturacion::query()
            ->when(Schema::hasColumn('facturacion', 'anulada'), fn ($q) => $q->noAnulada())
            ->whereBetween('fecha_factura', [$desde->toDateString(), $hasta->toDateString()])
            ->count();
    }

    private function sumCobrado(Carbon $desde, Carbon $hasta): float
    {
        if (! Schema::hasTable('conta_cobros')) {
            return 0.0;
        }

        return (float) ContaCobro::query()
            ->whereBetween('fecha_pago', [$desde->toDateString(), $hasta->toDateString()])
            ->sum('monto');
    }

    private function variacion(float $actual, float $anterior): array
    {
        if ($anterior <= 0.0) {
            return ['pct' => null, 'abs' => $actual - $anterior, 'direccion' => $actual > 0 ? 'up' : 'flat'];
        }
        $pct = (($actual - $anterior) / $anterior) * 100.0;

        return [
            'pct' => $pct,
            'abs' => $actual - $anterior,
            'direccion' => $pct > 0.5 ? 'up' : ($pct < -0.5 ? 'down' : 'flat'),
        ];
    }

    private function serieUltimosMeses(int $n, bool $incluirCobros): array
    {
        $serie = [];
        $cursor = now()->startOfMonth()->subMonths($n - 1);

        for ($i = 0; $i < $n; $i++) {
            $ini = $cursor->copy()->startOfMonth();
            $fin = $cursor->copy()->endOfMonth();
            $serie[] = [
                'label' => mb_convert_case($ini->isoFormat('MMM YY'), MB_CASE_TITLE, 'UTF-8'),
                'facturado' => round($this->sumFacturado($ini, $fin), 2),
                'cobrado' => $incluirCobros ? round($this->sumCobrado($ini, $fin), 2) : 0,
            ];
            $cursor->addMonth();
        }

        return $serie;
    }

    private function topClientesFacturados(Carbon $desde, Carbon $hasta): array
    {
        $facturas = Facturacion::query()
            ->with(['cliente:id,nombre_completo', 'encomienda.remitente:id,nombre_completo'])
            ->when(Schema::hasColumn('facturacion', 'anulada'), fn ($q) => $q->noAnulada())
            ->whereBetween('fecha_factura', [$desde->toDateString(), $hasta->toDateString()])
            ->get(['id', 'cliente_id', 'encomienda_id', 'monto_total', 'fecha_factura']);

        $facturaIds = $facturas->pluck('id')->all();
        $cobrosPorFactura = [];
        if (Schema::hasTable('conta_cobros') && ! empty($facturaIds)) {
            $cobrosPorFactura = ContaCobro::query()
                ->whereIn('factura_id', $facturaIds)
                ->select('factura_id', DB::raw('SUM(monto) AS total'))
                ->groupBy('factura_id')
                ->pluck('total', 'factura_id')
                ->toArray();
        }

        $acumulado = [];
        foreach ($facturas as $f) {
            $clienteNombre = $f->cliente?->nombre_completo;
            $remitenteNombre = $f->encomienda?->remitente?->nombre_completo;

            if ($clienteNombre) {
                $key = 'cli:'.$f->cliente_id;
                $nombre = $clienteNombre;
                $tipo = 'cliente';
            } elseif ($remitenteNombre) {
                $key = 'rem:'.$f->encomienda?->remitente?->id;
                $nombre = $remitenteNombre;
                $tipo = 'remitente';
            } else {
                $key = 'sn:'.$f->id;
                $nombre = 'Sin identificar (Factura '.$f->id.')';
                $tipo = 'sin';
            }

            if (! isset($acumulado[$key])) {
                $acumulado[$key] = [
                    'nombre' => $nombre,
                    'tipo' => $tipo,
                    'facturado' => 0.0,
                    'cobrado' => 0.0,
                    'facturas' => 0,
                ];
            }
            $acumulado[$key]['facturado'] += (float) $f->monto_total;
            $acumulado[$key]['cobrado'] += (float) ($cobrosPorFactura[$f->id] ?? 0.0);
            $acumulado[$key]['facturas']++;
        }

        foreach ($acumulado as &$row) {
            $row['saldo'] = max(0, $row['facturado'] - $row['cobrado']);
        }
        unset($row);

        usort($acumulado, fn ($a, $b) => $b['facturado'] <=> $a['facturado']);

        return array_slice($acumulado, 0, 10);
    }
}
