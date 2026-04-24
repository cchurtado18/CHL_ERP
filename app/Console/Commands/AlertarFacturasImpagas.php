<?php

namespace App\Console\Commands;

use App\Models\Facturacion;
use App\Models\Notificacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class AlertarFacturasImpagas extends Command
{
    protected $signature = 'facturacion:alertar-morosidad {--dry-run : Solo mostrar qué haría, sin crear notificaciones}';

    protected $description = 'Crea notificaciones para facturas con más de 3 días de antigüedad que siguen impagas';

    /** Estados que consideramos “no pagada / pendiente de cobro”. */
    private const ESTADOS_IMPAGOS = [
        'pendiente',
        'parcial',
        'entregado_sin_pagar',
        'facturado_npne',
    ];

    private const TITULO_NOTIFICACION = 'Factura impaga (>3 días)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $hoy = now()->startOfDay();
        /** Fecha de factura más antigua permitida: hoy − 3 días (desde el día 4º se considera morosidad). */
        $topeFechaFactura = $hoy->copy()->subDays(3);

        $facturas = Facturacion::query()
            ->when(Schema::hasColumn('facturacion', 'anulada'), fn ($q) => $q->noAnulada())
            ->whereIn('estado_pago', self::ESTADOS_IMPAGOS)
            ->whereDate('fecha_factura', '<=', $topeFechaFactura->toDateString())
            ->with('cliente')
            ->orderBy('id')
            ->get();

        if ($facturas->isEmpty()) {
            $this->info('No hay facturas impagas con más de 3 días según la fecha de factura.');

            return self::SUCCESS;
        }

        $usuarios = User::query()
            ->whereIn('rol', ['admin', 'agente'])
            ->get();

        if ($usuarios->isEmpty()) {
            $usuarios = User::all();
        }

        $creadas = 0;

        foreach ($facturas as $factura) {
            $dias = Carbon::parse($factura->fecha_factura)->startOfDay()->diffInDays($hoy);
            $clienteNombre = optional($factura->cliente)->nombre_completo ?? '—';
            $mensaje = sprintf(
                'La factura #%d (%s) lleva más de 3 días sin pago. Fecha de factura: %s (%d días). Monto: $%s. Estado: %s.',
                $factura->id,
                $clienteNombre,
                Carbon::parse($factura->fecha_factura)->format('d/m/Y'),
                $dias,
                number_format((float) $factura->monto_total, 2),
                str_replace('_', ' ', (string) $factura->estado_pago)
            );

            foreach ($usuarios as $usuario) {
                if ($this->yaNotificadoHoy($usuario->id, $factura->id)) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry-run] Usuario {$usuario->id}: Factura #{$factura->id}");

                    continue;
                }

                Notificacion::create([
                    'user_id' => $usuario->id,
                    'titulo' => self::TITULO_NOTIFICACION,
                    'mensaje' => $mensaje,
                    'leido' => false,
                    'fecha' => now(),
                ]);
                $creadas++;
            }
        }

        if ($dryRun) {
            $this->info("Dry-run: {$facturas->count()} factura(s) candidata(s). No se crearon notificaciones.");

            return self::SUCCESS;
        }

        $this->info("Listo. Se crearon {$creadas} notificación(es) para {$facturas->count()} factura(s).");

        return self::SUCCESS;
    }

    private function yaNotificadoHoy(int $userId, int $facturaId): bool
    {
        $needle = 'factura #'.$facturaId;

        return Notificacion::query()
            ->where('user_id', $userId)
            ->where('titulo', self::TITULO_NOTIFICACION)
            ->whereDate('fecha', now()->toDateString())
            ->whereRaw('LOWER(mensaje) LIKE ?', ['%'.$needle.'%'])
            ->exists();
    }
}
