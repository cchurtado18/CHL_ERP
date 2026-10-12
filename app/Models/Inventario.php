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
