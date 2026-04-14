<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notificaciones = Notificacion::with('usuario')
            ->orderBy('fecha', 'desc')
            ->paginate(10);
        
        return view('notificaciones.index', compact('notificaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $usuarios = User::all();
        return view('notificaciones.create', compact('usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
        ]);

        Notificacion::create([
            'user_id' => $request->user_id,
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'leido' => false,
            'fecha' => now(),
        ]);

        return redirect()->route('notificaciones.index')
            ->with('success', 'Notificación creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Notificacion $notificacion)
    {
        if (Auth::check()
            && (int) $notificacion->user_id === (int) Auth::id()
            && ! $notificacion->leido) {
            $notificacion->update(['leido' => true]);
            $notificacion->refresh();
        }

        return view('notificaciones.show', compact('notificacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notificacion $notificacion)
    {
        $usuarios = User::all();
        return view('notificaciones.edit', compact('notificacion', 'usuarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notificacion $notificacion)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'leido' => 'boolean',
        ]);

        $notificacion->update([
            'user_id' => $request->user_id,
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'leido' => $request->has('leido'),
        ]);

        return redirect()->route('notificaciones.index')
            ->with('success', 'Notificación actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notificacion $notificacion)
    {
        $notificacion->delete();

        return redirect()->route('notificaciones.index')
            ->with('success', 'Notificación eliminada exitosamente.');
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida(Notificacion $notificacion)
    {
        $notificacion->update(['leido' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Obtener notificaciones no leídas del usuario autenticado
     */
    public function noLeidas()
    {
        $notificaciones = Notificacion::where('user_id', Auth::id())
            ->where('leido', false)
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json($notificaciones);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasLeidas()
    {
        Notificacion::where('user_id', Auth::id())
            ->where('leido', false)
            ->update(['leido' => true]);

        return response()->json(['success' => true]);
    }
} 