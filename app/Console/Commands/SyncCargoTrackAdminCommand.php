<?php

namespace App\Console\Commands;

use App\Services\Tracking\TrackingSyncService;
use Illuminate\Console\Command;

class SyncCargoTrackAdminCommand extends Command
{
    protected $signature = 'tracking:sync-admin';

    protected $description = 'Sincroniza todos los paquetes CargoTrack (acceso admin) y los asigna por agencia';

    public function handle(TrackingSyncService $sync): int
    {
        $this->info('Sincronizando CargoTrack (admin)...');
        $stats = $sync->syncAdmin();

        $this->line(sprintf(
            'listed=%d inbound=%d assigned=%d unmapped=%d created=%d updated=%d docs=%d conflicts=%d',
            $stats['listed'],
            $stats['inbound'],
            $stats['assigned'],
            $stats['unmapped'],
            $stats['created'],
            $stats['updated'],
            $stats['documents'],
            $stats['conflicts']
        ));

        foreach ($stats['errors'] as $err) {
            $this->warn(' · '.$err);
        }

        return $stats['errors'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
