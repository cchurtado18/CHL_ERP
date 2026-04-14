<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Console\Command;

class RecordarSeguimientoLeads extends Command
{
    protected $signature = 'leads:recordar-seguimientos {--dry-run : Mostrar candidatos sin crear notificaciones}';

    protected $description = 'Genera recordatorios internos para leads con seguimiento vencido';

    private const TITULO = 'Seguimiento de lead pendiente';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $leads = Lead::query()
            ->where('resultado', 'abierto')
            ->whereNotNull('proximo_contacto_at')
            ->where('proximo_contacto_at', '<=', now())
            ->with('creador')
            ->orderBy('proximo_contacto_at')
            ->get();

        if ($leads->isEmpty()) {
            $this->info('No hay leads con seguimiento vencido.');

            return self::SUCCESS;
        }

        $usuarios = User::query()->whereIn('rol', ['admin', 'agente', 'basico'])->get();
        if ($usuarios->isEmpty()) {
            $usuarios = User::all();
        }

        $count = 0;
        foreach ($leads as $lead) {
            $mensaje = sprintf(
                'El lead %s (%s) tiene seguimiento pendiente desde %s. Campaña: %s. Etapa: %s.',
                $lead->codigo,
                $lead->nombre_completo,
                optional($lead->proximo_contacto_at)->format('d/m/Y H:i'),
                $lead->campana ?: 'Sin campaña',
                str_replace('_', ' ', $lead->etapa)
            );

            foreach ($usuarios as $usuario) {
                if ($this->yaNotificadoHoy($usuario->id, $lead->codigo)) {
                    continue;
                }
                if ($dryRun) {
                    $this->line("[dry-run] U{$usuario->id} Lead {$lead->codigo}");

                    continue;
                }

                Notificacion::create([
                    'user_id' => $usuario->id,
                    'titulo' => self::TITULO,
                    'mensaje' => $mensaje,
                    'leido' => false,
                    'fecha' => now(),
                ]);
                $count++;
            }

            if (! $dryRun) {
                $lead->update(['estado_recordatorio' => 'enviado']);
            }
        }

        if ($dryRun) {
            $this->info("Dry-run: {$leads->count()} lead(s) vencidos detectados.");

            return self::SUCCESS;
        }

        $this->info("Proceso completado. Notificaciones creadas: {$count}.");

        return self::SUCCESS;
    }

    private function yaNotificadoHoy(int $userId, string $leadCodigo): bool
    {
        $needle = 'lead '.strtolower($leadCodigo);

        return Notificacion::query()
            ->where('user_id', $userId)
            ->where('titulo', self::TITULO)
            ->whereDate('fecha', now()->toDateString())
            ->whereRaw('LOWER(mensaje) LIKE ?', ['%'.$needle.'%'])
            ->exists();
    }
}
