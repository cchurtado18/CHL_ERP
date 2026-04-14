<?php

namespace App\Http\Controllers;

use App\Models\AgendaEvento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaEventoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:5000',
            'fecha' => 'required|date',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i',
            'todo_el_dia' => 'sometimes|boolean',
            'ubicacion' => 'nullable|string|max:255',
            'owner_id' => 'nullable|exists:users,id',
            'mes' => 'nullable|string|max:7',
        ]);

        $todoElDia = $request->boolean('todo_el_dia');
        $fecha = Carbon::parse($data['fecha'])->startOfDay();

        if ($todoElDia) {
            $startsAt = $fecha->copy()->startOfDay();
            $endsAt = $fecha->copy()->endOfDay();
        } else {
            $horaInicio = $data['hora_inicio'] ?? '09:00';
            $startsAt = Carbon::parse($data['fecha'].' '.$horaInicio);
            if (! empty($data['hora_fin'])) {
                $endsAt = Carbon::parse($data['fecha'].' '.$data['hora_fin']);
                if ($endsAt->lte($startsAt)) {
                    return back()->withErrors(['hora_fin' => 'La hora fin debe ser posterior a la hora inicio.'])->withInput();
                }
            } else {
                $endsAt = null;
            }
        }

        $ownerId = isset($data['owner_id']) ? (int) $data['owner_id'] : null;
        if ($ownerId) {
            $allowed = \App\Models\User::query()->whereIn('rol', ['admin', 'agente', 'basico'])->whereKey($ownerId)->exists();
            if (! $allowed) {
                return back()->withErrors(['owner_id' => 'Responsable no válido.'])->withInput();
            }
        }

        AgendaEvento::create([
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'todo_el_dia' => $todoElDia,
            'ubicacion' => $data['ubicacion'] ?? null,
            'owner_id' => $ownerId,
            'created_by' => Auth::id(),
        ]);

        $mes = $data['mes'] ?? $startsAt->format('Y-m');
        $query = array_filter([
            'mes' => $mes,
            'etapa' => $request->input('etapa'),
            'origen' => $request->input('origen'),
            'campana' => $request->input('campana'),
            'owner_id' => $request->input('filter_owner_id'),
        ], fn ($v) => $v !== null && $v !== '');

        return redirect()->route('leads.calendar', $query)->with('success', 'Evento de agenda creado.');
    }

    public function destroy(Request $request, AgendaEvento $agendaEvento)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }
        $can = $user->rol === 'admin' || (int) $agendaEvento->created_by === (int) $user->id;
        if (! $can) {
            abort(403);
        }

        $mes = $request->input('mes', $agendaEvento->starts_at->format('Y-m'));
        $query = array_filter([
            'mes' => $mes,
            'etapa' => $request->input('etapa'),
            'origen' => $request->input('origen'),
            'campana' => $request->input('campana'),
            'owner_id' => $request->input('filter_owner_id'),
        ], fn ($v) => $v !== null && $v !== '');

        $agendaEvento->delete();

        return redirect()->route('leads.calendar', $query)->with('success', 'Evento eliminado.');
    }
}
