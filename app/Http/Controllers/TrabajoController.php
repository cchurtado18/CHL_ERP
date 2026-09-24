<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Trabajo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TrabajoController extends Controller
{
    public function calendar(Request $request)
    {
        $user = Auth::user();
        $mesInput = (string) $request->input('mes', now()->format('Y-m'));
        try {
            if (preg_match('/^\d{4}-\d{2}$/', $mesInput) === 1) {
                $base = Carbon::parse($mesInput.'-01')->startOfMonth();
            } else {
                $base = Carbon::parse($mesInput)->startOfMonth();
            }
        } catch (\Throwable $e) {
            $base = now()->startOfMonth();
        }

        $from = $base->copy()->startOfMonth()->startOfDay();
        $to = $base->copy()->endOfMonth()->endOfDay();
        $startGrid = $from->copy()->startOfWeek(Carbon::SUNDAY);
        $endGrid = $to->copy()->endOfWeek(Carbon::SATURDAY);

        $query = Trabajo::query()->with(['cliente', 'asignado', 'asignador'])
            ->whereBetween('fecha_programada', [$startGrid->copy()->startOfDay(), $endGrid->copy()->endOfDay()])
            ->orderBy('fecha_programada');

        $this->aplicarVisibilidad($query, $user, $request);

        if ($request->filled('estado')) {
            $query->where('estado', (string) $request->input('estado'));
        }
        if ($request->filled('prioridad')) {
            $query->where('prioridad', (string) $request->input('prioridad'));
        }

        $items = $query->get();
        $porDia = [];
        foreach ($items as $trabajo) {
            $key = $trabajo->fecha_programada->format('Y-m-d');
            $porDia[$key][] = $trabajo;
        }

        $equipo = $this->equipoAsignable();
        $clientes = Cliente::query()->orderBy('nombre_completo')->get(['id', 'nombre_completo']);
        $estados = Trabajo::ESTADOS;
        $prioridades = Trabajo::PRIORIDADES;
        $esAdmin = $user->esAdmin();

        $misPendientes = Trabajo::query()
            ->where('asignado_a', $user->id)
            ->where('estado', '!=', 'finalizado')
            ->count();

        return view('trabajos.calendar', [
            'baseMonth' => $base,
            'prevMonth' => $base->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $base->copy()->addMonth()->format('Y-m'),
            'porDia' => $porDia,
            'equipo' => $equipo,
            'clientes' => $clientes,
            'estados' => $estados,
            'prioridades' => $prioridades,
            'esAdmin' => $esAdmin,
            'misPendientes' => $misPendientes,
            'queryBase' => $request->only(['estado', 'prioridad', 'asignado_a']),
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Trabajo::query()->with(['cliente', 'asignado', 'asignador']);

        $this->aplicarVisibilidad($query, $user, $request);

        if ($request->filled('estado')) {
            $query->where('estado', (string) $request->input('estado'));
        }
        if ($request->filled('prioridad')) {
            $query->where('prioridad', (string) $request->input('prioridad'));
        }
        if ($request->filled('busqueda')) {
            $term = trim((string) $request->input('busqueda'));
            $query->where(function ($q) use ($term) {
                $q->where('titulo', 'like', "%{$term}%")
                    ->orWhere('descripcion', 'like', "%{$term}%")
                    ->orWhereHas('cliente', fn ($qc) => $qc->where('nombre_completo', 'like', "%{$term}%"));
            });
        }

        $trabajos = $query->orderByRaw("CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END")
            ->orderByRaw("CASE prioridad WHEN 'urgente' THEN 0 WHEN 'promedio' THEN 1 ELSE 2 END")
            ->orderBy('fecha_programada')
            ->paginate(15)
            ->appends($request->all());

        $equipo = $this->equipoAsignable();
        $clientes = Cliente::query()->orderBy('nombre_completo')->get(['id', 'nombre_completo']);
        $estados = Trabajo::ESTADOS;
        $prioridades = Trabajo::PRIORIDADES;
        $esAdmin = $user->esAdmin();

        return view('trabajos.index', compact('trabajos', 'equipo', 'clientes', 'estados', 'prioridades', 'esAdmin'));
    }

    public function create()
    {
        $this->autorizarAdmin();

        $clientes = Cliente::query()->orderBy('nombre_completo')->get(['id', 'nombre_completo']);
        $equipo = $this->equipoAsignable();

        return view('trabajos.create', compact('clientes', 'equipo'));
    }

    public function store(Request $request)
    {
        $this->autorizarAdmin();

        $data = $request->validate([
            'titulo' => 'required|string|max:200',
            'asignado_a' => ['required', 'exists:users,id', Rule::in($this->equipoAsignable()->pluck('id')->all())],
            'prioridad' => ['required', Rule::in(Trabajo::PRIORIDADES)],
            'cliente_id' => 'nullable|exists:clientes,id',
            'descripcion' => 'nullable|string|max:5000',
            'fecha' => 'nullable|date',
            'hora' => 'nullable|date_format:H:i',
        ]);

        $fecha = $data['fecha'] ?? now()->format('Y-m-d');
        $hora = $data['hora'] ?? '09:00';
        $fechaProgramada = Carbon::parse($fecha.' '.$hora);

        Trabajo::create([
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'cliente_id' => ! empty($data['cliente_id']) ? (int) $data['cliente_id'] : null,
            'asignado_a' => (int) $data['asignado_a'],
            'asignado_por' => Auth::id(),
            'fecha_programada' => $fechaProgramada,
            'estado' => 'asignado',
            'prioridad' => $data['prioridad'],
        ]);

        return redirect()
            ->route('leads.trabajos.calendar', ['mes' => $fechaProgramada->format('Y-m'), 'asignado_a' => $data['asignado_a']])
            ->with('success', 'Trabajo asignado correctamente.');
    }

    public function show($id)
    {
        $trabajo = Trabajo::with(['cliente', 'asignado', 'asignador'])->findOrFail($id);
        $this->autorizarVer($trabajo);

        $esAdmin = Auth::user()->esAdmin();
        $puedeCambiarEstado = $esAdmin || (int) $trabajo->asignado_a === (int) Auth::id();

        return view('trabajos.show', compact('trabajo', 'esAdmin', 'puedeCambiarEstado'));
    }

    public function updateEstado(Request $request, $id)
    {
        $trabajo = Trabajo::findOrFail($id);
        $user = Auth::user();
        $esAdmin = $user->esAdmin();
        $esAsignado = (int) $trabajo->asignado_a === (int) $user->id;

        if (! $esAdmin && ! $esAsignado) {
            abort(403, 'No puedes cambiar el estado de este trabajo.');
        }

        $data = $request->validate([
            'estado' => ['required', Rule::in(Trabajo::ESTADOS)],
            'nota_estado' => 'nullable|string|max:2000',
        ]);

        $trabajo->estado = $data['estado'];
        if (array_key_exists('nota_estado', $data)) {
            $trabajo->nota_estado = $data['nota_estado'];
        }

        if ($data['estado'] === 'visto' && ! $trabajo->visto_at) {
            $trabajo->visto_at = now();
        }
        if ($data['estado'] === 'finalizado') {
            $trabajo->finalizado_at = now();
        } else {
            $trabajo->finalizado_at = null;
        }

        $trabajo->save();

        return back()->with('success', 'Estado actualizado a: '.$trabajo->labelEstado());
    }

    public function destroy($id)
    {
        $this->autorizarAdmin();
        $trabajo = Trabajo::findOrFail($id);
        $mes = $trabajo->fecha_programada->format('Y-m');
        $trabajo->delete();

        return redirect()
            ->route('leads.trabajos.calendar', ['mes' => $mes])
            ->with('success', 'Trabajo eliminado.');
    }

    private function aplicarVisibilidad($query, User $user, Request $request): void
    {
        if ($user->esAdmin()) {
            if ($request->filled('asignado_a')) {
                $query->where('asignado_a', (int) $request->input('asignado_a'));
            }

            return;
        }

        $query->where('asignado_a', $user->id);
    }

    private function autorizarAdmin(): void
    {
        if (! Auth::user()?->esAdmin()) {
            abort(403, 'Solo el administrador puede asignar trabajos.');
        }
    }

    private function autorizarVer(Trabajo $trabajo): void
    {
        $user = Auth::user();
        if ($user->esAdmin() || (int) $trabajo->asignado_a === (int) $user->id) {
            return;
        }
        abort(403);
    }

    private function equipoAsignable()
    {
        return User::query()
            ->whereIn('rol', ['admin', 'agente', 'basico'])
            ->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'email', 'rol']);
    }
}
