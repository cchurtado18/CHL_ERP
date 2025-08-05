<?php

namespace App\Http\Controllers;

use App\Models\Tracking;
use App\Models\Cliente;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trackings = Tracking::with(['cliente', 'creador'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('tracking.index', compact('trackings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::all();
        $usuarios = User::all();
        return view('tracking.create', compact('clientes', 'usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tracking_codigo' => 'required|string|max:100|unique:tracking,tracking_codigo',
            'estado' => 'required|string|max:50',
            'recordatorio_fecha' => 'required|date|after:now',
            'nota' => 'nullable|string',
            'duracion_horas' => 'required|integer|min:1|max:720', // Máximo 30 días
        ]);

        $tracking = Tracking::create([
            'cliente_id' => $request->cliente_id,
            'tracking_codigo' => $request->tracking_codigo,
            'estado' => $request->estado,
            'fecha_estado' => now(),
            'recordatorio_fecha' => $request->recordatorio_fecha,
            'nota' => $request->nota,
            'creado_por' => Auth::id(),
        ]);

        // Crear notificación para el recordatorio
        $this->crearNotificacionRecordatorio($tracking, $request->recordatorio_fecha);

        return redirect()->route('tracking.index')
            ->with('success', 'Tracking creado exitosamente con temporizador configurado.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tracking = Tracking::with('cliente')->findOrFail($id);
        return view('tracking.show', compact('tracking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tracking $tracking)
    {
        $clientes = Cliente::all();
        $usuarios = User::all();
        return view('tracking.edit', compact('tracking', 'clientes', 'usuarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tracking $tracking)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tracking_codigo' => 'required|string|max:100|unique:tracking,tracking_codigo,' . $tracking->id,
            'estado' => 'required|string|max:50',
            'recordatorio_fecha' => 'required|date',
            'nota' => 'nullable|string',
        ]);

        $tracking->update([
            'cliente_id' => $request->cliente_id,
            'tracking_codigo' => $request->tracking_codigo,
            'estado' => $request->estado,
            'fecha_estado' => now(),
            'recordatorio_fecha' => $request->recordatorio_fecha,
            'nota' => $request->nota,
        ]);

        return redirect()->route('tracking.index')
            ->with('success', 'Tracking actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tracking = Tracking::findOrFail($id);
        $tracking->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Actualizar estado del tracking
     */
    public function actualizarEstado(Request $request, Tracking $tracking)
    {
        $request->validate([
            'estado' => 'required|string|max:50',
        ]);

        $tracking->update([
            'estado' => $request->estado,
            'fecha_estado' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'estado' => $request->estado,
            'fecha_estado' => now()->format('d/m/Y H:i:s')
        ]);
    }

    /**
     * Obtener tracking por código
     */
    public function buscarPorCodigo(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string'
        ]);

        $tracking = Tracking::with(['cliente', 'creador'])
            ->where('tracking_codigo', 'LIKE', '%' . $request->codigo . '%')
            ->first();

        if (!$tracking) {
            return response()->json([
                'success' => false,
                'message' => 'Tracking no encontrado'
            ]);
        }

        return response()->json([
            'success' => true,
            'tracking' => $tracking
        ]);
    }

    /**
     * Obtener trackings próximos a vencer
     */
    public function proximosVencer()
    {
        $trackings = Tracking::with(['cliente', 'creador'])
            ->where('recordatorio_fecha', '>', now())
            ->where('recordatorio_fecha', '<=', now()->addDays(7))
            ->where('estado', '!=', 'completado')
            ->orderBy('recordatorio_fecha', 'asc')
            ->get();

        return response()->json($trackings);
    }

    /**
     * Crear notificación de recordatorio
     */
    private function crearNotificacionRecordatorio($tracking, $fechaRecordatorio)
    {
        $usuarios = User::all();
        
        foreach ($usuarios as $usuario) {
            Notificacion::create([
                'user_id' => $usuario->id,
                'titulo' => 'Recordatorio de Tracking',
                'mensaje' => "El tracking {$tracking->tracking_codigo} para el cliente {$tracking->cliente->nombre} vence el " . Carbon::parse($fechaRecordatorio)->format('d/m/Y H:i'),
                'leido' => false,
                'fecha' => now(),
            ]);
        }
    }

    /**
     * Verificar recordatorios vencidos (para usar con cron job)
     */
    public function verificarRecordatorios()
    {
        $trackingsVencidos = Tracking::where('recordatorio_fecha', '<=', now())
            ->where('estado', '!=', 'completado')
            ->get();

        foreach ($trackingsVencidos as $tracking) {
            $this->crearNotificacionRecordatorio($tracking, $tracking->recordatorio_fecha);
            
            // Actualizar estado a vencido
            $tracking->update(['estado' => 'vencido']);
        }

        return response()->json([
            'success' => true,
            'message' => "Se procesaron {$trackingsVencidos->count()} recordatorios vencidos"
        ]);
    }

    /**
     * Dashboard de tracking
     */
    public function dashboard()
    {
        $totalTrackings = Tracking::count();
        $trackingsPendientes = Tracking::where('estado', 'pendiente')->count();
        $trackingsVencidos = Tracking::where('recordatorio_fecha', '<=', now())
            ->where('estado', '!=', 'completado')
            ->count();
        $trackingsCompletados = Tracking::where('estado', 'completado')->count();
        
        $proximosVencer = Tracking::with(['cliente'])
            ->where('recordatorio_fecha', '>', now())
            ->where('recordatorio_fecha', '<=', now()->addDays(7))
            ->where('estado', '!=', 'completado')
            ->orderBy('recordatorio_fecha', 'asc')
            ->take(5)
            ->get();

        $trackingsVencidosList = Tracking::with(['cliente'])
            ->where('recordatorio_fecha', '<=', now())
            ->where('estado', '!=', 'completado')
            ->orderBy('recordatorio_fecha', 'asc')
            ->take(10)
            ->get();

        return view('tracking.dashboard', compact(
            'totalTrackings',
            'trackingsPendientes',
            'trackingsVencidos',
            'trackingsCompletados',
            'proximosVencer',
            'trackingsVencidosList'
        ));
    }

    public function completar($id)
    {
        $tracking = Tracking::findOrFail($id);
        $tracking->estado = 'completado';
        $tracking->save();
        return response()->json(['success' => true]);
    }

    /**
     * Contar trackings vencidos para la notificación
     */
    public function countVencidos()
    {
        $count = Tracking::where('recordatorio_fecha', '<=', now())
            ->where('estado', '!=', 'completado')
            ->count();
        
        return response()->json([
            'count' => $count,
            'has_vencidos' => $count > 0
        ]);
    }
} 