<?php

namespace App\Http\Controllers;

use App\Models\ContaCobro;
use App\Models\ContaVoucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ContabilidadVoucherController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('conta_vouchers')) {
            return view('contabilidad.vouchers.index', [
                'items' => collect(),
                'setupPendiente' => true,
            ]);
        }

        $q = ContaVoucher::query()->with(['cuentaBancoCaja', 'creador']);
        if ($request->filled('tipo') && in_array($request->input('tipo'), ['ingreso', 'egreso'], true)) {
            $q->where('tipo', $request->input('tipo'));
        }
        if ($request->filled('q')) {
            $needle = '%'.addcslashes(trim((string) $request->input('q')), '%_\\').'%';
            $q->where(function ($sub) use ($needle) {
                $sub->where('folio', 'like', $needle)
                    ->orWhere('tercero_nombre', 'like', $needle)
                    ->orWhere('concepto', 'like', $needle);
            });
        }

        $items = $q->orderByDesc('fecha')->orderByDesc('id')->paginate(20)->appends($request->query());

        return view('contabilidad.vouchers.index', [
            'items' => $items,
            'setupPendiente' => false,
        ]);
    }

    public function show(int $id)
    {
        $voucher = ContaVoucher::query()->with(['cuentaBancoCaja', 'creador'])->findOrFail($id);

        return view('contabilidad.vouchers.show', compact('voucher'));
    }

    public function pdf(int $id)
    {
        $voucher = ContaVoucher::query()->with(['cuentaBancoCaja', 'creador'])->findOrFail($id);
        $pdf = Pdf::loadView('contabilidad.vouchers.pdf', compact('voucher'));

        return $pdf->stream('voucher_'.$voucher->folio.'.pdf');
    }

    public function pdfCobro(int $id)
    {
        $cobro = ContaCobro::query()->findOrFail($id);
        if (! Schema::hasColumn('conta_cobros', 'voucher_id') || ! $cobro->voucher_id) {
            return redirect()->route('contabilidad.cobros.show', $cobro->id)
                ->withErrors(['error' => 'Este cobro no tiene voucher. Los cobros nuevos sí lo generan.']);
        }

        return $this->pdf((int) $cobro->voucher_id);
    }
}
