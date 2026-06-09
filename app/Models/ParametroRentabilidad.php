<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParametroRentabilidad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parametros_rentabilidad';

    protected $fillable = [
        'costo_fijo_por_libra',
        'servicio_id',
        'moneda',
        'vigente_desde',
        'nota',
        'created_by',
    ];

    protected $casts = [
        'costo_fijo_por_libra' => 'decimal:4',
        'vigente_desde' => 'date',
    ];

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Parámetro vigente para un servicio específico a una fecha dada.
     * Si no hay valor configurado para ese servicio, devuelve el global (servicio_id = NULL).
     */
    public static function vigentePorServicio(?int $servicioId, Carbon|string|null $fecha = null): ?self
    {
        $fechaRef = $fecha ? Carbon::parse($fecha) : now();

        if ($servicioId !== null) {
            $especifico = static::query()
                ->where('servicio_id', $servicioId)
                ->where('vigente_desde', '<=', $fechaRef->toDateString())
                ->orderByDesc('vigente_desde')
                ->orderByDesc('id')
                ->first();

            if ($especifico) {
                return $especifico;
            }
        }

        return static::query()
            ->whereNull('servicio_id')
            ->where('vigente_desde', '<=', $fechaRef->toDateString())
            ->orderByDesc('vigente_desde')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Costo por libra para un servicio dado a una fecha. Si no hay configuración, devuelve 0.
     */
    public static function costoPorLibraServicio(?int $servicioId, Carbon|string|null $fecha = null): float
    {
        return (float) (static::vigentePorServicio($servicioId, $fecha)?->costo_fijo_por_libra ?? 0);
    }

    /**
     * Devuelve el último parámetro vigente "global" (sin servicio).
     */
    public static function vigenteGlobal(): ?self
    {
        return static::vigentePorServicio(null);
    }

    /**
     * @deprecated Mantener compatibilidad: ahora siempre apunta al global.
     */
    public static function vigenteEn(Carbon|string|null $fecha = null): ?self
    {
        return static::vigentePorServicio(null, $fecha);
    }

    /**
     * @deprecated Mantener compatibilidad.
     */
    public static function costoActualPorLibra(): float
    {
        return (float) (static::vigenteEn()?->costo_fijo_por_libra ?? 0);
    }
}
