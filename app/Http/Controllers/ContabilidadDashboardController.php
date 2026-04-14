<?php

namespace App\Http\Controllers;

use App\Models\ContaCobro;
use App\Models\ContaCxc;
use App\Models\Facturacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ContabilidadDashboardController extends Controller
{
    public function index(Request $request)
    {
        $setupPendiente = ! Schema::hasTable('conta_cobros') || ! Schema::hasTable('conta_cxc');
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $facturadoMes = (float) Facturacion::query()
            ->whereBetween('fecha_factura', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->sum('monto_total');

        $cobradoMes = 0.0;
        $saldoCxc = 0.0;
        $aging = ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0];
        $cxcPendientes = collect();
        $facturasContabilidadPendiente = 0;

        if (! $setupPendiente) {
            $cobradoMes = (float) ContaCobro::query()
                ->whereBetween('fecha_pago', [$inicioMes->toDateString(), $finMes->toDateString()])
                ->sum('monto');

            $cxcRows = ContaCxc::query()
                ->where('saldo_actual', '>', 0)
                ->get(['saldo_actual', 'fecha_emision']);

            $saldoCxc = (float) $cxcRows->sum('saldo_actual');
            foreach ($cxcRows as $row) {
                $dias = (int) $row->fecha_emision?->diffInDays(now());
                if ($dias <= 30) {
                    $aging['0_30']++;
                } elseif ($dias <= 60) {
                    $aging['31_60']++;
                } elseif ($dias <= 90) {
                    $aging['61_90']++;
                } else {
                    $aging['90_plus']++;
                }
            }

            $cxcPendientes = ContaCxc::query()
                ->with(['factura.cliente', 'factura.encomienda.remitente'])
                ->where('saldo_actual', '>', 0)
                ->orderByDesc('fecha_emision')
                ->orderByDesc('factura_id')
                ->limit(12)
                ->get();

            if (Schema::hasColumn('facturacion', 'contabilidad_pendiente')) {
                $facturasContabilidadPendiente = (int) Facturacion::query()
                    ->where('contabilidad_pendiente', true)
                    ->count();
            }
        }

        return view('contabilidad.dashboard', compact('facturadoMes', 'cobradoMes', 'saldoCxc', 'aging', 'setupPendiente', 'cxcPendientes', 'facturasContabilidadPendiente'));
    }
}
