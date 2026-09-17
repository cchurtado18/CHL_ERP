<?php

namespace App\Console\Commands;

use App\Models\Inventario;
use App\Services\PrimetrackClient;
use Illuminate\Console\Command;

class SyncPaqueteEstados extends Command
{
    protected $signature = 'paquetes:sync-estados
                            {--limit=100 : Máximo de paquetes a sincronizar}
                            {--id= : Sincronizar un inventario_id específico}';

    protected $description = 'Sincroniza estados de paquetes desde Primetrack Group';

    public function handle(PrimetrackClient $client): int
    {
        if (! $client->configured()) {
            $this->warn('Primetrack no está configurado (PRIMETRACK_BASE_URL / PRIMETRACK_API_KEY).');

            return self::SUCCESS;
        }

        $query = Inventario::query()
            ->where(function ($q) {
                $q->whereNotNull('tracking_codigo')->where('tracking_codigo', '!=', '')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('numero_guia')->where('numero_guia', '!=', '');
                    });
            })
            ->where(function ($q) {
                $q->whereNull('estado')
                    ->orWhere('estado', '!=', 'entregado');
            });

        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        }

        $limit = (int) $this->option('limit');
        $paquetes = $query->orderByDesc('updated_at')->limit($limit)->get();

        $this->info('Sincronizando '.$paquetes->count().' paquete(s)...');

        $ok = 0;
        $fail = 0;
        foreach ($paquetes as $paquete) {
            try {
                if ($client->syncPaquete($paquete)) {
                    $ok++;
                    $this->line("  ✓ #{$paquete->id} actualizado");
                }
            } catch (\Throwable $e) {
                $fail++;
                $this->error("  ✗ #{$paquete->id}: ".$e->getMessage());
            }
        }

        $this->info("Listo. Cambios: {$ok}. Errores: {$fail}.");

        return self::SUCCESS;
    }
}
