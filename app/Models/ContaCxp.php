<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaCxp extends Model
{
    use HasFactory;

    protected $table = 'conta_cxp';

    protected $fillable = [
        'proveedor_id',
        'numero_documento',
        'descripcion',
        'fecha_emision',
        'fecha_vencimiento',
        'dias_credito',
        'monto_original',
        'saldo_actual',
        'moneda',
        'tasa_cambio',
        'cuenta_gasto_id',
        'estado_pago',
        'origen',
        'asiento_id',
        'created_by',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'monto_original' => 'decimal:2',
        'saldo_actual' => 'decimal:2',
        'tasa_cambio' => 'decimal:4',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function cuentaGasto()
    {
        return $this->belongsTo(ContaCuenta::class, 'cuenta_gasto_id');
    }

    public function pagos()
    {
        return $this->hasMany(ContaPagoProveedor::class, 'cxp_id')->orderByDesc('fecha_pago')->orderByDesc('id');
    }

    public function paquetes()
    {
        return $this->hasMany(Inventario::class, 'cxp_id')->orderByDesc('id');
    }

    public function asiento()
    {
        return $this->belongsTo(ContaAsiento::class, 'asiento_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function etiquetaDocumento(): string
    {
        return $this->numero_documento ? (string) $this->numero_documento : ('CXP-'.$this->id);
    }

    public function montoPaquetesAsignados(): float
    {
        if (isset($this->attributes['monto_paquetes'])) {
            return round((float) $this->attributes['monto_paquetes'], 2);
        }

        return round((float) $this->paquetes()->sum('monto_calculado'), 2);
    }

    public function diferenciaPaquetesVsFactura(): float
    {
        return round((float) $this->monto_original - $this->montoPaquetesAsignados(), 2);
    }
}
