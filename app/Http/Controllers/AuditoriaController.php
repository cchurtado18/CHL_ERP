<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\LogInventario;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $rows = $this->filasUnificadas($request);
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 25;
        $total = $rows->count();
        $slice = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        $logs = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $modulos = $rows->pluck('modulo')->unique()->sort()->values();
        $acciones = $rows->pluck('accion')->unique()->sort()->values();
        $creaciones = $rows->where('accion', 'crear')->count();
        $ediciones = $rows->where('accion', 'editar')->count();
        $eliminaciones = $rows->where('accion', 'eliminar')->count();

        return view('auditoria.index', compact(
            'logs',
            'modulos',
            'acciones',
            'creaciones',
            'ediciones',
            'eliminaciones'
        ));
    }

    private function filasUnificadas(Request $request): Collection
    {
        $sistema = collect();
        if (Schema::hasTable('logs')) {
            $q = Log::query()->with('usuario');
            if ($request->filled('usuario')) {
                $needle = '%'.$request->input('usuario').'%';
                $q->whereHas('usuario', fn ($u) => $u->where('nombre', 'like', $needle)->orWhere('email', 'like', $needle));
            }
            if ($request->filled('modulo')) {
                $q->where('modulo', $request->input('modulo'));
            }
            if ($request->filled('accion')) {
                $q->where('accion', $request->input('accion'));
            }
            if ($request->filled('desde')) {
                $q->whereDate('fecha', '>=', $request->input('desde'));
            }
            if ($request->filled('hasta')) {
                $q->whereDate('fecha', '<=', $request->input('hasta'));
            }

            $sistema = $q->orderByDesc('id')->limit(2000)->get()->map(function (Log $log) {
                $detalle = json_decode((string) $log->descripcion, true);
                if (! is_array($detalle)) {
                    $detalle = ['resumen' => (string) $log->descripcion];
                }

                return (object) [
                    'id' => 's-'.$log->id,
                    'fecha' => $log->fecha ?: $log->created_at,
                    'usuario' => $log->usuario,
                    'modulo' => $log->modulo,
                    'accion' => $log->accion,
                    'resumen' => $this->resumenSistema($log->modulo, $log->accion, $detalle),
                    'detalle' => $detalle,
                    'guia' => $detalle['payload']['numero_guia'] ?? ($detalle['params']['id'] ?? null),
                    'fuente' => 'sistema',
                ];
            });
        }

        $inventario = collect();
        $incluyeInventario = ! $request->filled('modulo') || $request->input('modulo') === 'inventario';
        if ($incluyeInventario && Schema::hasTable('logs_inventario')) {
            $q = LogInventario::query()->with(['usuario', 'inventario']);
            if ($request->filled('usuario')) {
                $needle = '%'.$request->input('usuario').'%';
                $q->whereHas('usuario', fn ($u) => $u->where('nombre', 'like', $needle)->orWhere('email', 'like', $needle));
            }
            if ($request->filled('accion') && in_array($request->input('accion'), ['crear', 'editar'], true)) {
                $q->where('accion', $request->input('accion'));
            } elseif ($request->filled('accion')) {
                $q->whereRaw('1 = 0');
            }
            if ($request->filled('desde')) {
                $q->whereDate('created_at', '>=', $request->input('desde'));
            }
            if ($request->filled('hasta')) {
                $q->whereDate('created_at', '<=', $request->input('hasta'));
            }

            $inventario = $q->latest()->limit(2000)->get()->map(function (LogInventario $log) {
                return (object) [
                    'id' => 'i-'.$log->id,
                    'fecha' => $log->created_at,
                    'usuario' => $log->usuario,
                    'modulo' => 'inventario',
                    'accion' => $log->accion,
                    'resumen' => ($log->accion === 'crear' ? 'Paquete creado' : 'Paquete editado')
                        .' · guía '.($log->inventario->numero_guia ?? $log->despues['numero_guia'] ?? '—'),
                    'detalle' => [
                        'guia' => $log->inventario->numero_guia ?? null,
                        'antes' => $log->antes,
                        'despues' => $log->despues,
                    ],
                    'guia' => $log->inventario->numero_guia ?? ($log->despues['numero_guia'] ?? null),
                    'fuente' => 'inventario',
                ];
            });
        }

        return $sistema->concat($inventario)
            ->sortByDesc(fn ($row) => optional($row->fecha)?->timestamp ?? 0)
            ->values();
    }

    private function resumenSistema(string $modulo, string $accion, array $detalle): string
    {
        $ruta = $detalle['ruta'] ?? $detalle['url'] ?? $modulo;
        $verbo = match ($accion) {
            'crear' => 'Creó',
            'editar' => 'Editó',
            'eliminar' => 'Eliminó',
            default => 'Registró',
        };

        return $verbo.' en '.$this->etiquetaModulo($modulo).' · '.$ruta;
    }

    public static function etiquetaModulo(string $modulo): string
    {
        $map = [
            'inventario' => 'Inventario',
            'facturacion' => 'Facturación',
            'clientes' => 'Clientes',
            'encomiendas' => 'Encomiendas',
            'leads' => 'Leads',
            'contabilidad' => 'Contabilidad',
            'usuarios' => 'Usuarios',
            'tracking' => 'Tracking',
            'notificaciones' => 'Notificaciones',
            'remitentes' => 'Remitentes',
            'destinatarios' => 'Destinatarios',
            'trabajos' => 'Trabajos',
            'portal' => 'Portal',
            'tarifas-clientes' => 'Tarifas',
        ];

        return $map[$modulo] ?? ucfirst(str_replace('-', ' ', $modulo));
    }
}
