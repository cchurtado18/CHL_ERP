<?php

namespace App\Models;

use App\Support\TrackingCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';

    protected $fillable = [
        'cliente_id',
        'servicio_id',
        'factura_id',
        'peso_lb',
        'tarifa_manual',
        'monto_calculado',
        'fecha_ingreso',
        'estado',
        'numero_guia',
        'notas',
        'created_by',
        'updated_by',
        'tracking_codigo',
    ];

    protected $casts = [
        'tarifa_manual' => 'decimal:2',
        'monto_calculado' => 'decimal:2',
    ];

    /**
     * Muestra 2 decimales como siempre. Solo deja 3 o 4 si el peso
     * realmente los tiene (el usuario los escribió).
     */
    public static function formatPeso(mixed $peso): string
    {
        if ($peso === null || $peso === '') {
            return '0.00';
        }

        $as4 = number_format((float) $peso, 4, '.', '');
        $frac = substr($as4, strpos($as4, '.') + 1);

        if (substr($frac, 2, 2) === '00') {
            return number_format((float) $peso, 2, '.', '');
        }

        return rtrim($as4, '0');
    }

    protected function pesoLb(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === null || $value === '') {
                    return null;
                }

                return (float) $value;
            },
            set: function ($value) {
                if ($value === null || $value === '') {
                    return null;
                }

                return round((float) $value, 4);
            },
        );
    }

    public static function calcularMonto(float $peso, float $tarifa): float
    {
        return round($peso * $tarifa, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Estado que ve el cliente (rastreo y portal):
     * facturado → "entregado"; ingresado pero sin factura → "en_oficina".
     */
    public function estadoCliente(): string
    {
        return $this->factura_id ? 'entregado' : 'en_oficina';
    }

    public function estadoClienteLabel(): string
    {
        return $this->factura_id ? 'Entregado' : 'En oficina';
    }

    public function estadoClienteBadge(): string
    {
        return $this->factura_id ? 'bg-emerald-200 text-emerald-900' : 'bg-amber-200 text-amber-900';
    }

    public function montoFacturable(): float
    {
        if ($this->monto_calculado !== null && $this->monto_calculado !== '') {
            return round((float) $this->monto_calculado, 2, PHP_ROUND_HALF_UP);
        }

        return self::calcularMonto(
            (float) ($this->peso_lb ?? 0),
            (float) ($this->tarifa_manual ?? 1)
        );
    }

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function factura()
    {
        return $this->belongsTo(Facturacion::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function estadoEventos()
    {
        return $this->hasMany(PaqueteEstadoEvento::class, 'inventario_id')->orderByDesc('evento_at');
    }

    public static function findByPublicCode(string $code): ?self
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        $variants = TrackingCode::variants($code);
        $exact = static::query()
            ->where(function (Builder $q) use ($variants) {
                foreach ($variants as $variant) {
                    $q->orWhere('tracking_codigo', $variant)
                        ->orWhere('numero_guia', $variant);
                }
            })
            ->first();

        if ($exact) {
            return $exact;
        }

        $norm = TrackingCode::normalize($code);
        if (strlen($norm) < 6) {
            return null;
        }

        return static::query()
            ->where(function (Builder $q) use ($norm) {
                $normalizedSql = "UPPER(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(%s,''), ' ', ''), '-', ''), '/', ''), '_', ''))";
                $trackExpr = sprintf($normalizedSql, 'tracking_codigo');
                $guiaExpr = sprintf($normalizedSql, 'numero_guia');

                $q->whereRaw($trackExpr.' = ?', [$norm])
                    ->orWhereRaw($guiaExpr.' = ?', [$norm])
                    ->orWhereRaw($trackExpr.' IN (?, ?)', ['WRH'.$norm, 'NO'.$norm])
                    ->orWhereRaw($guiaExpr.' IN (?, ?)', ['WRH'.$norm, 'NO'.$norm]);

                if (strlen($norm) >= 12 && ! TrackingCode::isNumericGuia($norm)) {
                    $prefix = substr($norm, 0, min(18, strlen($norm)));
                    $q->orWhereRaw($trackExpr.' LIKE ?', [$prefix.'%']);
                }

                if (strlen($norm) >= 12) {
                    $q->orWhereRaw($trackExpr.' LIKE ?', ['%'.$norm]);
                }
            })
            ->first();
    }

    protected function trackingCodigo(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value === null) {
                    return null;
                }
                $trimmed = trim((string) $value);

                return $trimmed === '' ? null : $trimmed;
            }
        );
    }

    protected function numeroGuia(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value === null) {
                    return null;
                }
                $trimmed = trim((string) $value);

                return $trimmed === '' ? null : $trimmed;
            }
        );
    }
}
