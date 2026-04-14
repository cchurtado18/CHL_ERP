<?php

namespace App\Http\Controllers;

use App\Models\ContaPeriodo;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ContabilidadPeriodoController extends Controller
{
    public function index()
    {
        $periodos = ContaPeriodo::query()->orderByDesc('anio')->orderByDesc('mes')->paginate(24);

        return view('contabilidad.periodos.index', compact('periodos'));
    }

    public function toggleEstado($id)
    {
        if (Auth::user()?->rol !== 'admin') {
            abort(403, 'Solo un administrador puede cerrar o reabrir períodos.');
        }

        $periodo = ContaPeriodo::findOrFail($id);
        if ($periodo->estado === 'abierto') {
            $periodo->estado = 'cerrado';
            $periodo->fecha_cierre = now();
            $periodo->cerrado_por = Auth::id();
        } else {
            $periodo->estado = 'abierto';
            $periodo->fecha_cierre = null;
            $periodo->cerrado_por = null;
        }
        $periodo->save();
        $accion = $periodo->estado === 'cerrado' ? 'cerrado' : 'reabierto';

        $destinatarios = User::query()->whereIn('rol', ['admin', 'agente'])->pluck('id');
        foreach ($destinatarios as $uid) {
            Notificacion::create([
                'user_id' => $uid,
                'titulo' => 'Contabilidad: cambio de período',
                'mensaje' => "El período {$periodo->anio}-{$periodo->mes} fue {$accion}.",
                'leido' => 0,
                'fecha' => now(),
            ]);
        }

        return back()->with('success', 'Estado del período actualizado.');
    }
}
