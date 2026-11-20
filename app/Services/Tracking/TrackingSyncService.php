<?php

namespace App\Services\Tracking;

use App\Contracts\TrackingProvider;
use App\Models\CargotrackAgencia;
use App\Models\CargotrackInbound;
use App\Models\CargotrackSetting;
use App\Models\Cliente;
use App\Models\Inventario;
use App\Models\PaqueteEstadoEvento;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrackingSyncService
{
    public function __construct(protected TrackingProvider $provider) {}

    /**
     * Sync admin: lista warehouse completa → inbound → asigna por agencia (CHLOGIX/SHIPIA) → inventario.
     *
     * @return array{
     *   listed:int,inbound:int,assigned:int,unmapped:int,created:int,updated:int,
     *   documents:int,conflicts:int,errors:list<string>
     * }
     */
    public function syncAdmin(?CargoTrackAdminClient $admin = null): array
    {
        $stats = [
            'listed' => 0,
            'inbound' => 0,
            'assigned' => 0,
            'unmapped' => 0,
            'created' => 0,
            'updated' => 0,
            'documents' => 0,
            'conflicts' => 0,
            'errors' => [],
        ];

        $settings = CargotrackSetting::current();
        if (! $settings->sync_enabled) {
            $stats['errors'][] = 'Sync admin CargoTrack desactivado. No se intentará login en GC.';

            return $stats;
        }
        if (! $settings->hasCredentials()) {
            $stats['errors'][] = 'Faltan credenciales admin de CargoTrack.';

            return $stats;
        }
        if (! $this->provider instanceof CargoTrackProvider) {
            $stats['errors'][] = 'El provider activo no es CargoTrack.';

            return $stats;
        }

        $cooldownMinutes = (int) config('services.cargotrack.login_cooldown_minutes', 15);
        $cooldownKey = 'cargotrack.admin_login_cooldown';
        if (Cache::has($cooldownKey)) {
            $seconds = max(1, (int) (Cache::get($cooldownKey.'.until', now()->timestamp) - now()->timestamp));
            $mins = (int) ceil($seconds / 60);
            $stats['errors'][] = "Cooldown activo tras un fallo de login (~{$mins} min). "
                .'No se reintentará para evitar bloqueos en CargoTrack. Prueba primero entrar en el navegador.';

            return $stats;
        }

        $admin ??= new CargoTrackAdminClient($this->provider);

        if (! $admin->login((string) $settings->admin_username, (string) $settings->admin_password)) {
            $detail = $admin->lastLoginError()
                ?: 'No se pudo iniciar sesión en CargoTrack con las credenciales admin.';
            $stats['errors'][] = $detail;
            Cache::put($cooldownKey, true, now()->addMinutes($cooldownMinutes));
            Cache::put($cooldownKey.'.until', now()->addMinutes($cooldownMinutes)->timestamp, now()->addMinutes($cooldownMinutes));
            $settings->forceFill([
                'last_sync_at' => now(),
                'last_sync_message' => 'Login fallido (cooldown '.$cooldownMinutes.' min): '.$detail,
            ])->save();

            return $stats;
        }

        Cache::forget($cooldownKey);
        Cache::forget($cooldownKey.'.until');

        $rows = $admin->listWarehousePackages();
        $stats['listed'] = count($rows);

        $agencias = CargotrackAgencia::query()
            ->where('activo', true)
            ->get()
            ->keyBy(fn (CargotrackAgencia $a) => CargotrackAgencia::normalizeCodigo($a->codigo));

        foreach ($rows as $row) {
            try {
                $row = $admin->enrichWithLookup($row);
                $inbound = $this->upsertInbound($row, $agencias);
                $stats['inbound']++;

                if ($settings->fetch_documents && empty($inbound->documento_path)) {
                    $doc = $admin->fetchDocument(
                        (string) $inbound->warehouse,
                        $inbound->detail_url
                    );
                    if ($doc) {
                        $inbound->documento_path = $doc['path'];
                        $inbound->documento_mime = $doc['mime'];
                        $inbound->documento_url_origen = $doc['url'];
                        $inbound->save();
                        $stats['documents']++;
                    }
                }

                if ($inbound->assign_status === 'unmapped' || ! $inbound->cliente_id) {
                    $stats['unmapped']++;

                    continue;
                }

                $stats['assigned']++;
                $cliente = Cliente::find($inbound->cliente_id);
                if (! $cliente) {
                    $stats['unmapped']++;

                    continue;
                }

                $pkg = $this->inboundToPackageArray($inbound);
                $result = $this->upsertPackage($cliente, $pkg);
                if ($result === 'created') {
                    $stats['created']++;
                } elseif ($result === 'updated') {
                    $stats['updated']++;
                } elseif ($result === 'conflicts') {
                    $stats['conflicts']++;
                }

                // Vincular inbound → inventario y copiar metadata GC
                $inventario = Inventario::query()
                    ->where('cliente_id', $cliente->id)
                    ->where(function ($q) use ($inbound) {
                        $q->where('numero_guia', $inbound->warehouse);
                        if ($inbound->tracking_codigo) {
                            $q->orWhere('tracking_codigo', $inbound->tracking_codigo);
                        }
                    })
                    ->first();

                if ($inventario) {
                    $inventario->fill([
                        'cargotrack_agencia' => $inbound->agencia_codigo,
                        'cargotrack_consignee' => $inbound->consignee_nombre,
                        'cargotrack_shipper' => $inbound->shipper,
                        'cargotrack_modalidad' => $inbound->modalidad,
                        'piezas' => $inbound->piezas,
                        'volumen' => $inbound->volumen,
                        'documento_path' => $inbound->documento_path ?? $inventario->documento_path,
                        'documento_mime' => $inbound->documento_mime ?? $inventario->documento_mime,
                        'cargotrack_synced_at' => now(),
                    ]);
                    if ($inbound->modalidad && ! $inventario->servicio_id) {
                        $inventario->servicio_id = $this->resolveServicioId($inbound->modalidad);
                    }
                    $inventario->save();

                    $inbound->inventario_id = $inventario->id;
                    $inbound->assign_status = 'assigned';
                    $inbound->save();
                }
            } catch (\Throwable $e) {
                $stats['errors'][] = ($row['warehouse'] ?? '?').': '.$e->getMessage();
                Log::error('CargoTrack admin sync row failed', [
                    'row' => $row['warehouse'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $msg = sprintf(
            'listed=%d inbound=%d assigned=%d unmapped=%d created=%d updated=%d docs=%d conflicts=%d',
            $stats['listed'],
            $stats['inbound'],
            $stats['assigned'],
            $stats['unmapped'],
            $stats['created'],
            $stats['updated'],
            $stats['documents'],
            $stats['conflicts']
        );
        $settings->forceFill([
            'last_sync_at' => now(),
            'last_sync_message' => $msg,
        ])->save();

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  \Illuminate\Support\Collection<string, CargotrackAgencia>  $agencias
     */
    protected function upsertInbound(array $row, $agencias): CargotrackInbound
    {
        $warehouse = trim((string) ($row['warehouse'] ?? ''));
        $agenciaCodigo = CargotrackAgencia::normalizeCodigo((string) ($row['agencia_codigo'] ?? ''));
        $agencia = $agenciaCodigo !== '' ? ($agencias->get($agenciaCodigo)) : null;

        $assignStatus = 'unmapped';
        $clienteId = null;
        if ($agencia && $agencia->cliente_id) {
            $assignStatus = 'assigned';
            $clienteId = $agencia->cliente_id;
        } elseif ($agenciaCodigo === '') {
            $assignStatus = 'pending';
        }

        return CargotrackInbound::updateOrCreate(
            ['warehouse' => $warehouse],
            [
                'tracking_codigo' => $row['tracking_codigo'] ?? null,
                'agencia_codigo' => $agenciaCodigo !== '' ? $agenciaCodigo : null,
                'consignee_nombre' => $row['consignee_nombre'] ?? null,
                'shipper' => $row['shipper'] ?? null,
                'modalidad' => $row['modalidad'] ?? null,
                'fecha_warehouse' => $row['fecha_warehouse'] ?? null,
                'piezas' => $row['piezas'] ?? null,
                'peso_lb' => $row['peso_lb'] ?? null,
                'volumen' => $row['volumen'] ?? null,
                'valor' => $row['valor'] ?? null,
                'estado' => $row['estado'] ?? null,
                'status_raw' => $row['status_raw'] ?? null,
                'descripcion' => $row['descripcion'] ?? null,
                'detail_url' => $row['detail_url'] ?? null,
                'cliente_id' => $clienteId,
                'assign_status' => $assignStatus,
                'raw' => [
                    'cells' => $row['raw_cells'] ?? null,
                    'events' => $row['events'] ?? null,
                ],
                'synced_at' => now(),
            ]
        );
    }

    /**
     * Asigna un inbound a un cliente CRM y lo promueve/actualiza en inventario.
     *
     * @return array{ok:bool,result?:string,inventario_id?:int,error?:string}
     */
    public function assignInboundToCliente(CargotrackInbound $inbound, Cliente $cliente): array
    {
        try {
            $pkg = $this->inboundToPackageArray($inbound);
            $result = $this->upsertPackage($cliente, $pkg);

            if ($result === 'conflicts') {
                return [
                    'ok' => false,
                    'result' => $result,
                    'error' => 'La guía/tracking ya pertenece a otro cliente.',
                ];
            }

            $inventario = Inventario::query()
                ->where('cliente_id', $cliente->id)
                ->where(function ($q) use ($inbound) {
                    $q->where('numero_guia', $inbound->warehouse);
                    if ($inbound->tracking_codigo) {
                        $q->orWhere('tracking_codigo', $inbound->tracking_codigo);
                    }
                })
                ->first();

            if (! $inventario) {
                return [
                    'ok' => false,
                    'result' => $result,
                    'error' => 'No se pudo crear/actualizar el paquete en inventario.',
                ];
            }

            $inventario->fill([
                'cargotrack_agencia' => $inbound->agencia_codigo,
                'cargotrack_consignee' => $inbound->consignee_nombre,
                'cargotrack_shipper' => $inbound->shipper,
                'cargotrack_modalidad' => $inbound->modalidad,
                'piezas' => $inbound->piezas,
                'volumen' => $inbound->volumen,
                'documento_path' => $inbound->documento_path ?? $inventario->documento_path,
                'documento_mime' => $inbound->documento_mime ?? $inventario->documento_mime,
                'cargotrack_synced_at' => now(),
            ]);
            if ($inbound->modalidad && ! $inventario->servicio_id) {
                $inventario->servicio_id = $this->resolveServicioId($inbound->modalidad);
            }
            $inventario->save();

            $inbound->cliente_id = $cliente->id;
            $inbound->inventario_id = $inventario->id;
            $inbound->assign_status = 'assigned';
            $inbound->save();

            return [
                'ok' => true,
                'result' => $result,
                'inventario_id' => $inventario->id,
            ];
        } catch (\Throwable $e) {
            Log::error('CargoTrack assign inbound failed', [
                'inbound_id' => $inbound->id,
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{ok:int,fail:int,errors:list<string>}
     */
    public function assignInboundBulk(array $inboundIds, Cliente $cliente): array
    {
        $ok = 0;
        $fail = 0;
        $errors = [];

        $rows = CargotrackInbound::query()->whereIn('id', $inboundIds)->get();
        foreach ($rows as $inbound) {
            $result = $this->assignInboundToCliente($inbound, $cliente);
            if ($result['ok']) {
                $ok++;
            } else {
                $fail++;
                $errors[] = $inbound->warehouse.': '.($result['error'] ?? 'error');
            }
        }

        return compact('ok', 'fail', 'errors');
    }

    /**
     * @return array<string, mixed>
     */
    protected function inboundToPackageArray(CargotrackInbound $inbound): array
    {
        return [
            'tracking_number' => $inbound->tracking_codigo ?: $inbound->warehouse,
            'wrh' => $inbound->warehouse,
            'status' => $inbound->estado ?: 'recibido',
            'status_raw' => $inbound->status_raw,
            'weight' => $inbound->peso_lb !== null ? (float) $inbound->peso_lb : null,
            'description' => $inbound->descripcion,
            'pickup_date' => optional($inbound->fecha_warehouse)->toDateString(),
            'events' => is_array($inbound->raw['events'] ?? null) ? $inbound->raw['events'] : [],
            'agencia' => $inbound->agencia_codigo,
            'consignee' => $inbound->consignee_nombre,
            'shipper' => $inbound->shipper,
            'modalidad' => $inbound->modalidad,
            'piezas' => $inbound->piezas,
            'volumen' => $inbound->volumen,
        ];
    }

    protected function resolveServicioId(string $modalidad): ?int
    {
        $modalidad = Str::lower($modalidad);
        $needle = $modalidad === 'maritimo' ? 'marit' : 'aer';

        $servicio = Servicio::query()
            ->get()
            ->first(function (Servicio $s) use ($needle) {
                $t = Str::lower(strtr((string) $s->tipo_servicio, [
                    'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                ]));

                return str_contains($t, $needle);
            });

        return $servicio?->id;
    }

    /**
     * @return array{created:int,updated:int,skipped:int,conflicts:int,errors:list<string>}
     */
    public function syncCliente(Cliente $cliente): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'conflicts' => 0,
            'errors' => [],
        ];

        if (! $this->provider->enabled()) {
            $stats['errors'][] = 'Proveedor de tracking deshabilitado.';

            return $stats;
        }

        if (! $cliente->cargotrack_sync_enabled) {
            $stats['errors'][] = 'Sincronización CargoTrack desactivada para este cliente.';

            return $stats;
        }

        $username = (string) ($cliente->cargotrack_username ?? '');
        $password = (string) ($cliente->cargotrack_password ?? '');

        if ($username === '' || $password === '') {
            $stats['errors'][] = 'Faltan credenciales CargoTrack del cliente.';

            return $stats;
        }

        $remotePackages = $this->provider->listPackagesForCredentials($username, $password);

        // También refrescar paquetes locales existentes vía lookup público.
        $localCodes = Inventario::query()
            ->where('cliente_id', $cliente->id)
            ->whereNotNull('tracking_codigo')
            ->where('tracking_codigo', '!=', '')
            ->pluck('tracking_codigo')
            ->all();

        $byCode = [];
        foreach ($remotePackages as $pkg) {
            $code = trim((string) ($pkg['tracking_number'] ?? ''));
            if ($code !== '') {
                $byCode[strtoupper($code)] = $pkg;
            }
        }

        foreach ($localCodes as $code) {
            $key = strtoupper((string) $code);
            if (isset($byCode[$key])) {
                continue;
            }
            $detail = $this->provider->lookup((string) $code);
            if ($detail) {
                $byCode[$key] = $detail;
            }
        }

        foreach ($byCode as $pkg) {
            try {
                $result = $this->upsertPackage($cliente, $pkg);
                $stats[$result]++;
            } catch (\Throwable $e) {
                $stats['errors'][] = ($pkg['tracking_number'] ?? '?').': '.$e->getMessage();
                Log::error('Tracking sync package failed', [
                    'cliente_id' => $cliente->id,
                    'package' => $pkg['tracking_number'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $cliente->forceFill([
            'cargotrack_last_sync_at' => now(),
        ])->save();

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $pkg
     * @return 'created'|'updated'|'skipped'|'conflicts'
     */
    protected function upsertPackage(Cliente $cliente, array $pkg): string
    {
        $guia = trim((string) ($pkg['wrh'] ?? ''));
        $tracking = trim((string) ($pkg['tracking_number'] ?? ''));

        // Preferir tracking largo; si solo hay guía de 6 dígitos, usarla también como código de match.
        if ($tracking === '' && $guia === '') {
            return 'skipped';
        }
        if ($tracking === '') {
            $tracking = $guia;
        }

        return DB::transaction(function () use ($cliente, $pkg, $tracking, $guia) {
            $existingOther = Inventario::query()
                ->where('cliente_id', '!=', $cliente->id)
                ->where(function ($q) use ($tracking, $guia) {
                    $q->where('tracking_codigo', $tracking);
                    if ($guia !== '') {
                        $q->orWhere('numero_guia', $guia)
                            ->orWhere('tracking_codigo', $guia);
                    }
                })
                ->first();

            if ($existingOther) {
                Log::warning('Tracking sync conflict: tracking/guía pertenece a otro cliente', [
                    'tracking' => $tracking,
                    'guia' => $guia,
                    'cliente_id' => $cliente->id,
                    'owner_cliente_id' => $existingOther->cliente_id,
                ]);

                return 'conflicts';
            }

            $inventario = Inventario::query()
                ->where('cliente_id', $cliente->id)
                ->where(function ($q) use ($tracking, $guia) {
                    $q->where('tracking_codigo', $tracking);
                    if ($guia !== '') {
                        $q->orWhere('numero_guia', $guia)
                            ->orWhere('tracking_codigo', $guia);
                    }
                })
                ->first();

            $estado = (string) ($pkg['status'] ?? 'pendiente');
            $peso = isset($pkg['weight']) ? (float) $pkg['weight'] : null;
            $notas = $this->buildNotas($pkg);
            $fechaIngreso = $this->parseDate($pkg['pickup_date'] ?? null) ?? now()->toDateString();
            $numeroGuia = $this->resolveNumeroGuia($pkg, $inventario?->id);

            if (! $inventario) {
                $inventario = Inventario::create([
                    'cliente_id' => $cliente->id,
                    'servicio_id' => null,
                    'factura_id' => null,
                    'tracking_codigo' => $tracking,
                    'numero_guia' => $numeroGuia,
                    'estado' => $estado,
                    'peso_lb' => $peso,
                    'notas' => $notas,
                    'fecha_ingreso' => $fechaIngreso,
                    'tarifa_manual' => null,
                    'monto_calculado' => null,
                ]);
                $this->syncEvents($inventario, $pkg);
                $this->appendEventIfNeeded($inventario, $estado, $pkg);

                return 'created';
            }

            // Update solo campos operativos; nunca facturación.
            $dirty = false;
            if ($estado !== '' && $inventario->estado !== $estado) {
                $inventario->estado = $estado;
                $dirty = true;
            }
            if ($peso !== null && (float) $inventario->peso_lb !== $peso) {
                $inventario->peso_lb = $peso;
                $dirty = true;
            }
            if ($notas !== null && $notas !== '' && $inventario->notas !== $notas) {
                $inventario->notas = $notas;
                $dirty = true;
            }
            // Corregir si el tracking largo llegó después (antes se guardó la guía como tracking).
            if ($tracking !== '' && $inventario->tracking_codigo !== $tracking) {
                if (empty($inventario->tracking_codigo)
                    || ($guia !== '' && $inventario->tracking_codigo === $guia)
                    || preg_match('/^\d{5,9}$/', (string) $inventario->tracking_codigo)) {
                    $inventario->tracking_codigo = $tracking;
                    $dirty = true;
                }
            }
            if (empty($inventario->fecha_ingreso) && $fechaIngreso) {
                $inventario->fecha_ingreso = $fechaIngreso;
                $dirty = true;
            }
            if ($numeroGuia && $inventario->numero_guia !== $numeroGuia) {
                if (empty($inventario->numero_guia) || ($guia !== '' && $numeroGuia === $guia)) {
                    $inventario->numero_guia = $numeroGuia;
                    $dirty = true;
                }
            }

            if ($dirty) {
                $inventario->save();
            }

            $eventsChanged = $this->syncEvents($inventario, $pkg);
            if ($dirty || $eventsChanged) {
                $this->appendEventIfNeeded($inventario, $estado, $pkg);

                return 'updated';
            }

            return 'skipped';
        });
    }

    /**
     * @param  array<string, mixed>  $pkg
     */
    protected function buildNotas(array $pkg): ?string
    {
        $parts = [];
        if (! empty($pkg['description'])) {
            $parts[] = (string) $pkg['description'];
        }
        if (! empty($pkg['status_raw'])) {
            $parts[] = 'Estado GC: '.$pkg['status_raw'];
        }

        return $parts === [] ? null : implode(' · ', $parts);
    }

    /**
     * @param  array<string, mixed>  $pkg
     */
    protected function resolveNumeroGuia(array $pkg, ?int $ignoreId = null): ?string
    {
        $wrh = trim((string) ($pkg['wrh'] ?? ''));
        if ($wrh !== '' && strlen($wrh) >= 6 && strlen($wrh) <= 9) {
            $exists = Inventario::query()
                ->where('numero_guia', $wrh)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists();
            if (! $exists) {
                return $wrh;
            }
        }

        // Generar guía interna única 8 dígitos.
        for ($i = 0; $i < 20; $i++) {
            $candidate = (string) random_int(10000000, 99999999);
            $exists = Inventario::query()
                ->where('numero_guia', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists();
            if (! $exists) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $pkg
     */
    protected function syncEvents(Inventario $inventario, array $pkg): bool
    {
        $events = $pkg['events'] ?? [];
        if (! is_array($events) || $events === []) {
            return false;
        }

        $changed = false;
        foreach ($events as $evento) {
            if (! is_array($evento)) {
                continue;
            }
            $estadoRaw = (string) ($evento['status'] ?? $evento['description'] ?? '');
            $estado = $this->mapEventEstado($estadoRaw, (string) ($pkg['status'] ?? 'en_transito'));
            $eventoAt = $this->parseDateTime($evento['evento_at'] ?? $evento['date'] ?? null) ?? now();

            $exists = PaqueteEstadoEvento::query()
                ->where('inventario_id', $inventario->id)
                ->where('estado_origen', 'cargotrack')
                ->where('estado', $estado)
                ->where('evento_at', $eventoAt)
                ->exists();

            if ($exists) {
                continue;
            }

            PaqueteEstadoEvento::create([
                'inventario_id' => $inventario->id,
                'estado' => $estado,
                'estado_origen' => 'cargotrack',
                'ubicacion' => null,
                'descripcion' => $estadoRaw !== '' ? $estadoRaw : ($pkg['status_raw'] ?? null),
                'evento_at' => $eventoAt,
                'payload' => $evento,
            ]);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @param  array<string, mixed>  $pkg
     */
    protected function appendEventIfNeeded(Inventario $inventario, string $estado, array $pkg): void
    {
        if ($estado === '') {
            return;
        }

        $latest = PaqueteEstadoEvento::query()
            ->where('inventario_id', $inventario->id)
            ->where('estado_origen', 'cargotrack')
            ->orderByDesc('evento_at')
            ->first();

        if ($latest && $latest->estado === $estado) {
            return;
        }

        PaqueteEstadoEvento::create([
            'inventario_id' => $inventario->id,
            'estado' => $estado,
            'estado_origen' => 'cargotrack',
            'ubicacion' => null,
            'descripcion' => $pkg['status_raw'] ?? $estado,
            'evento_at' => now(),
            'payload' => [
                'status_raw' => $pkg['status_raw'] ?? null,
                'source' => 'sync',
            ],
        ]);
    }

    protected function mapEventEstado(string $raw, string $fallback): string
    {
        if ($this->provider instanceof CargoTrackProvider) {
            return $this->provider->mapStatus($raw !== '' ? $raw : $fallback);
        }

        $key = Str::lower(trim($raw));
        if ($key === '') {
            return $fallback;
        }

        return match (true) {
            str_contains($key, 'deliver') => 'entregado',
            str_contains($key, 'destination') || str_contains($key, 'destino') => 'en_aduana',
            str_contains($key, 'transit') => 'en_transito',
            str_contains($key, 'receiv') || str_contains($key, 'recib') => 'recibido',
            default => $fallback,
        };
    }

    protected function parseDate(mixed $value): ?string
    {
        $dt = $this->parseDateTime($value);

        return $dt?->toDateString();
    }

    protected function parseDateTime(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
