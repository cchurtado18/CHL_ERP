<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Services\Tracking\TrackingSyncService;
use Illuminate\Console\Command;

class SyncClienteTrackingCommand extends Command
{
    protected $signature = 'tracking:sync-cliente
                            {cliente_id? : ID del cliente (subagencia)}
                            {--all : Sincronizar todos los clientes con sync activo}';

    protected $description = 'Sincroniza paquetes desde CargoTrack hacia el inventario local del cliente';

    public function handle(TrackingSyncService $sync): int
    {
        $clientes = collect();

        if ($this->option('all')) {
            $clientes = Cliente::query()
                ->where('cargotrack_sync_enabled', true)
                ->whereNotNull('cargotrack_username')
                ->where('cargotrack_username', '!=', '')
                ->get();
        } elseif ($this->argument('cliente_id')) {
            $cliente = Cliente::find($this->argument('cliente_id'));
            if (! $cliente) {
                $this->error('Cliente no encontrado.');

                return self::FAILURE;
            }
            $clientes = collect([$cliente]);
        } else {
            $this->error('Indica {cliente_id} o usa --all.');

            return self::FAILURE;
        }

        if ($clientes->isEmpty()) {
            $this->warn('No hay clientes para sincronizar.');

            return self::SUCCESS;
        }

        foreach ($clientes as $cliente) {
            $this->info("Sincronizando cliente #{$cliente->id} ({$cliente->nombre_completo})...");
            $stats = $sync->syncCliente($cliente);
            $this->line(sprintf(
                '  creados=%d actualizados=%d omitidos=%d conflictos=%d',
                $stats['created'],
                $stats['updated'],
                $stats['skipped'],
                $stats['conflicts']
            ));
            foreach ($stats['errors'] as $err) {
                $this->warn('  · '.$err);
            }
        }

        return self::SUCCESS;
    }
}
