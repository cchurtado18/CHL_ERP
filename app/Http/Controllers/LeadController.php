<?php

namespace App\Http\Controllers;

use App\Models\AgendaEvento;
use App\Models\Lead;
use App\Models\LeadInteraccion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::query()->with(['creador', 'owner']);

        if ($request->filled('busqueda')) {
            $term = trim((string) $request->input('busqueda'));
            $query->where(function ($q) use ($term) {
                $q->where('codigo', 'like', "%{$term}%")
                    ->orWhere('nombre_completo', 'like', "%{$term}%")
                    ->orWhere('telefono', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('campana', 'like', "%{$term}%");
            });
        }
        if ($request->filled('etapa')) {
            $query->where('etapa', $request->string('etapa'));
        }
        if ($request->filled('origen')) {
            $query->where('origen', $request->string('origen'));
        }
        if ($request->filled('campana')) {
            $query->where('campana', $request->string('campana'));
        }
        if ($request->filled('owner_id')) {
            $query->where('owner_id', (int) $request->input('owner_id'));
        }

        $leads = $query->orderByRaw('CASE WHEN proximo_contacto_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('proximo_contacto_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->appends($request->all());

        $kpis = $this->kpis();
        $etapas = Lead::ETAPAS;
        $origenes = Lead::query()->whereNotNull('origen')->where('origen', '<>', '')->distinct()->orderBy('origen')->pluck('origen');
        $campanas = Lead::query()->whereNotNull('campana')->where('campana', '<>', '')->distinct()->orderBy('campana')->pluck('campana');
        $owners = User::query()->whereIn('rol', ['admin', 'agente', 'basico'])->orderBy('nombre')->get(['id', 'nombre', 'email']);

        return view('leads.index', compact('leads', 'kpis', 'etapas', 'origenes', 'campanas', 'owners'));
    }

    public function calendar(Request $request)
    {
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

        $from = $base->copy()->startOfMonth();
        $to = $base->copy()->endOfMonth();

        $query = Lead::query()->with('owner')
            ->whereNotNull('proximo_contacto_at')
            ->whereBetween('proximo_contacto_at', [$from, $to])
            ->where('resultado', 'abierto')
            ->orderBy('proximo_contacto_at');
        $querySinFecha = Lead::query()->with('owner')
            ->whereNull('proximo_contacto_at')
            ->where('resultado', 'abierto')
            ->orderByDesc('created_at');

        if ($request->filled('etapa')) {
            $etapa = (string) $request->string('etapa');
            $query->where('etapa', $etapa);
            $querySinFecha->where('etapa', $etapa);
        }
        if ($request->filled('origen')) {
            $origen = (string) $request->string('origen');
            $query->where('origen', $origen);
            $querySinFecha->where('origen', $origen);
        }
        if ($request->filled('campana')) {
            $campana = (string) $request->string('campana');
            $query->where('campana', $campana);
            $querySinFecha->where('campana', $campana);
        }
        if ($request->filled('owner_id')) {
            $ownerId = (int) $request->input('owner_id');
            $query->where('owner_id', $ownerId);
            $querySinFecha->where('owner_id', $ownerId);
        }

        $items = $query->get();
        $porDia = [];
        foreach ($items as $lead) {
            $day = optional($lead->proximo_contacto_at)->format('Y-m-d');
            if (! $day) {
                continue;
            }
            if (! isset($porDia[$day])) {
                $porDia[$day] = [];
            }
            $porDia[$day][] = $lead;
        }

        $startGrid = $from->copy()->startOfWeek(Carbon::SUNDAY);
        $endGrid = $to->copy()->endOfWeek(Carbon::SATURDAY);
        $gridStart = $startGrid->copy()->startOfDay();
        $gridEnd = $endGrid->copy()->endOfDay();

        $eventosQuery = AgendaEvento::query()->with(['owner', 'creador']);
        if ($request->filled('owner_id')) {
            $ownerId = (int) $request->input('owner_id');
            $eventosQuery->where(function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId)->orWhereNull('owner_id');
            });
        }
        $eventos = $eventosQuery
            ->where('starts_at', '<=', $gridEnd)
            ->where(function ($q) use ($gridStart) {
                $q->where(function ($q2) use ($gridStart) {
                    $q2->whereNull('ends_at')->where('starts_at', '>=', $gridStart);
                })->orWhere(function ($q2) use ($gridStart) {
                    $q2->whereNotNull('ends_at')->where('ends_at', '>=', $gridStart);
                });
            })
            ->orderBy('starts_at')
            ->get();

        $eventosPorDia = [];
        foreach ($eventos as $ev) {
            $start = $ev->starts_at->copy()->startOfDay();
            $end = $ev->ends_at ? $ev->ends_at->copy()->startOfDay() : $start->copy();
            if ($end->lt($start)) {
                $end = $start->copy();
            }
            if ($start->lt($startGrid)) {
                $start = $startGrid->copy();
            }
            if ($end->gt($endGrid)) {
                $end = $endGrid->copy();
            }
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->format('Y-m-d');
                if (! isset($eventosPorDia[$key])) {
                    $eventosPorDia[$key] = [];
                }
                $eventosPorDia[$key][] = $ev;
            }
        }

        $cursor = $startGrid->copy();
        $itemsPorDia = [];
        while ($cursor->lte($endGrid)) {
            $key = $cursor->format('Y-m-d');
            $merged = [];
            foreach ($porDia[$key] ?? [] as $lead) {
                $merged[] = ['type' => 'lead', 'sort' => $lead->proximo_contacto_at->timestamp, 'lead' => $lead];
            }
            foreach ($eventosPorDia[$key] ?? [] as $ev) {
                $merged[] = ['type' => 'evento', 'sort' => $ev->starts_at->timestamp, 'evento' => $ev];
            }
            usort($merged, fn ($a, $b) => $a['sort'] <=> $b['sort']);
            $itemsPorDia[$key] = $merged;
            $cursor->addDay();
        }

        $kpis = $this->kpis();
        $etapas = Lead::ETAPAS;
        $origenes = Lead::query()->whereNotNull('origen')->where('origen', '<>', '')->distinct()->orderBy('origen')->pluck('origen');
        $campanas = Lead::query()->whereNotNull('campana')->where('campana', '<>', '')->distinct()->orderBy('campana')->pluck('campana');
        $owners = User::query()->whereIn('rol', ['admin', 'agente', 'basico'])->orderBy('nombre')->get(['id', 'nombre', 'email']);

        $sinFecha = $querySinFecha->limit(20)->get();
        $queryBase = $request->only(['etapa', 'origen', 'campana', 'owner_id']);

        return view('leads.calendar', [
            'baseMonth' => $base,
            'prevMonth' => $base->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $base->copy()->addMonth()->format('Y-m'),
            'itemsPorDia' => $itemsPorDia,
            'sinFecha' => $sinFecha,
            'queryBase' => $queryBase,
            'kpis' => $kpis,
            'etapas' => $etapas,
            'origenes' => $origenes,
            'campanas' => $campanas,
            'owners' => $owners,
        ]);
    }

    public function create()
    {
        $owners = User::query()->whereIn('rol', ['admin', 'agente', 'basico'])->orderBy('nombre')->get(['id', 'nombre', 'email']);

        return view('leads.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateLead($request);
        $validated['codigo'] = Lead::nextCodigo();
        $validated['created_by'] = Auth::id();
        $validated['owner_id'] = $validated['owner_id'] ?? Auth::id();
        $lead = Lead::create($validated);

        $this->registrarInteraccionSistema($lead, 'Lead creado en el sistema.');

        return redirect()->route('leads.show', $lead->id)->with('success', 'Lead creado correctamente.');
    }

    public function show($id)
    {
        $lead = Lead::with(['interacciones.creador', 'creador', 'editor', 'owner'])->findOrFail($id);

        return view('leads.show', compact('lead'));
    }

    public function edit($id)
    {
        $lead = Lead::findOrFail($id);
        $owners = User::query()->whereIn('rol', ['admin', 'agente', 'basico'])->orderBy('nombre')->get(['id', 'nombre', 'email']);

        return view('leads.edit', compact('lead', 'owners'));
    }

    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $oldEtapa = $lead->etapa;

        $validated = $this->validateLead($request, true);
        $validated['updated_by'] = Auth::id();
        $this->aplicarReglasDeCierre($lead, $validated);
        $lead->update($validated);

        if ($oldEtapa !== $lead->etapa) {
            $this->registrarInteraccionSistema($lead, "Etapa actualizada de {$oldEtapa} a {$lead->etapa}.");
        } else {
            $this->registrarInteraccionSistema($lead, 'Datos del lead actualizados.');
        }

        return redirect()->route('leads.show', $lead->id)->with('success', 'Lead actualizado correctamente.');
    }

    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead eliminado correctamente.');
    }

    public function storeInteraccion(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $data = $request->validate([
            'tipo' => 'required|in:llamada,whatsapp,correo,nota,reunion',
            'detalle' => 'required|string|max:5000',
            'fecha_interaccion' => 'nullable|date',
            'proximo_contacto_at' => 'nullable|date',
        ]);

        LeadInteraccion::create([
            'lead_id' => $lead->id,
            'tipo' => $data['tipo'],
            'detalle' => trim($data['detalle']),
            'fecha_interaccion' => ! empty($data['fecha_interaccion']) ? Carbon::parse($data['fecha_interaccion']) : now(),
            'created_by' => Auth::id(),
        ]);

        if (! empty($data['proximo_contacto_at'])) {
            $lead->update([
                'proximo_contacto_at' => Carbon::parse($data['proximo_contacto_at']),
                'ultimo_contacto_at' => now(),
                'estado_recordatorio' => 'pendiente',
                'updated_by' => Auth::id(),
            ]);
        } else {
            $lead->update([
                'ultimo_contacto_at' => now(),
                'updated_by' => Auth::id(),
            ]);
        }

        return redirect()->route('leads.show', $lead->id)->with('success', 'Interacción registrada correctamente.');
    }

    public function cambiarEtapa(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $data = $request->validate([
            'etapa' => 'required|in:'.implode(',', Lead::ETAPAS),
            'motivo_perdida' => 'nullable|string|max:180',
            'motivo_perdida_clave' => 'nullable|in:'.implode(',', Lead::MOTIVOS_PERDIDA),
        ]);

        $old = $lead->etapa;
        $payload = [
            'etapa' => $data['etapa'],
            'updated_by' => Auth::id(),
        ];
        $this->aplicarReglasDeCierre($lead, $payload, $data['motivo_perdida'] ?? null, $data['motivo_perdida_clave'] ?? null);
        $lead->update($payload);

        $this->registrarInteraccionSistema($lead, "Etapa actualizada de {$old} a {$lead->etapa}.");

        return back()->with('success', 'Etapa actualizada.');
    }

    public function marcarContactadoRapido(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $data = $request->validate([
            'resumen' => 'required|string|max:1200',
            'tipo' => 'nullable|in:llamada,whatsapp,correo,reunion,nota',
            'proximo_contacto_at' => 'nullable|date',
            'etapa_siguiente' => 'nullable|in:'.implode(',', Lead::ETAPAS),
        ]);

        LeadInteraccion::create([
            'lead_id' => $lead->id,
            'tipo' => $data['tipo'] ?? 'llamada',
            'detalle' => trim($data['resumen']),
            'fecha_interaccion' => now(),
            'created_by' => Auth::id(),
        ]);

        $payload = [
            'ultimo_contacto_at' => now(),
            'updated_by' => Auth::id(),
        ];
        if (! empty($data['proximo_contacto_at'])) {
            $payload['proximo_contacto_at'] = Carbon::parse($data['proximo_contacto_at']);
            $payload['estado_recordatorio'] = 'pendiente';
        }
        if (! empty($data['etapa_siguiente'])) {
            $payload['etapa'] = $data['etapa_siguiente'];
            $this->aplicarReglasDeCierre($lead, $payload);
        }

        $lead->update($payload);

        return back()->with('success', 'Seguimiento registrado. Lead actualizado.');
    }

    private function validateLead(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'direccion_cliente' => 'nullable|string|max:255',
            'campana' => 'nullable|string|max:160',
            'origen' => 'nullable|string|max:100',
            'estado_usa_origen' => 'nullable|string|max:120',
            'departamento_destino' => 'nullable|string|max:120',
            'municipio_destino' => 'nullable|string|max:120',
            'owner_id' => 'nullable|exists:users,id',
            'etapa' => 'required|in:'.implode(',', Lead::ETAPAS),
            'interes_servicio' => 'nullable|string|max:120',
            'presupuesto_estimado' => 'nullable|numeric|min:0',
            'proximo_contacto_at' => 'nullable|date',
            'ultimo_contacto_at' => 'nullable|date',
            'motivo_perdida' => 'nullable|string|max:180',
            'motivo_perdida_clave' => 'nullable|in:'.implode(',', Lead::MOTIVOS_PERDIDA),
            'notas' => 'nullable|string|max:5000',
        ]);
    }

    private function aplicarReglasDeCierre(Lead $lead, array &$payload, ?string $motivoPerdida = null, ?string $motivoPerdidaClave = null): void
    {
        $etapa = $payload['etapa'] ?? $lead->etapa;
        if ($etapa === 'convertido') {
            $payload['resultado'] = 'convertido';
            $payload['fecha_cierre'] = now();
            $payload['estado_recordatorio'] = 'completado';

            return;
        }
        if ($etapa === 'perdido') {
            $payload['resultado'] = 'perdido';
            $payload['fecha_cierre'] = now();
            $payload['estado_recordatorio'] = 'completado';
            if ($motivoPerdida !== null && trim($motivoPerdida) !== '') {
                $payload['motivo_perdida'] = trim($motivoPerdida);
            }
            if ($motivoPerdidaClave !== null && trim($motivoPerdidaClave) !== '') {
                $payload['motivo_perdida_clave'] = trim($motivoPerdidaClave);
            }

            return;
        }

        $payload['resultado'] = 'abierto';
        $payload['fecha_cierre'] = null;
        $payload['motivo_perdida'] = null;
        $payload['motivo_perdida_clave'] = null;
        if (($payload['proximo_contacto_at'] ?? $lead->proximo_contacto_at) !== null) {
            $payload['estado_recordatorio'] = 'pendiente';
        }
    }

    private function registrarInteraccionSistema(Lead $lead, string $detalle): void
    {
        LeadInteraccion::create([
            'lead_id' => $lead->id,
            'tipo' => 'sistema',
            'detalle' => $detalle,
            'fecha_interaccion' => now(),
            'created_by' => Auth::id(),
        ]);
    }

    private function kpis(): array
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();
        $nuevosMes = Lead::query()->whereBetween('created_at', [$inicioMes, $finMes])->count();
        $vencidos = Lead::query()
            ->where('resultado', 'abierto')
            ->whereNotNull('proximo_contacto_at')
            ->where('proximo_contacto_at', '<', now())
            ->count();
        $totalMes = Lead::query()->whereBetween('created_at', [$inicioMes, $finMes])->count();
        $convertidosMes = Lead::query()
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->where('resultado', 'convertido')
            ->count();
        $tasa = $totalMes > 0 ? round(($convertidosMes / $totalMes) * 100, 1) : 0;
        $porCampana = Lead::query()
            ->selectRaw('campana, count(*) as total')
            ->whereNotNull('campana')
            ->where('campana', '<>', '')
            ->groupBy('campana')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        $topMotivosPerdida = Lead::query()
            ->selectRaw('motivo_perdida_clave, count(*) as total')
            ->where('resultado', 'perdido')
            ->whereNotNull('motivo_perdida_clave')
            ->groupBy('motivo_perdida_clave')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'nuevos_mes' => $nuevosMes,
            'vencidos' => $vencidos,
            'tasa_conversion' => $tasa,
            'por_campana' => $porCampana,
            'motivos_perdida' => $topMotivosPerdida,
        ];
    }
}
