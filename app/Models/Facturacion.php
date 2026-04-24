<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturacion extends Model
{
    use HasFactory;

    protected $table = 'facturacion';

    protected $casts = [
        'contabilidad_pendiente' => 'boolean',
        'anulada' => 'boolean',
        'anulada_at' => 'datetime',
    ];

    protected $fillable = [
        'cliente_id',
        'encomienda_id',
        'fecha_factura',
        'numero_acta',
        'tipo_factura',
        'monto_total',
        'moneda',
        'tasa_cambio',
        'monto_local',
        'estado_pago',
        'contabilidad_pendiente',
        'nota',
        'created_by',
        'updated_by',
        'delivery',
        'cantidad_paquetes',
        'entrega_nombre',
        'entrega_cedula',
        'entrega_telefono',
        'anulada',
        'anulada_at',
        'anulada_por',
        'anulacion_motivo',
    ];

    // Cliente relacionado
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class);
    }

    // Usuario que creó la factura
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Usuario que la actualizó
    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Paquetes facturados
    public function paquetes()
    {
        return $this->hasMany(Inventario::class, 'factura_id');
    }

    // Pagos asociados
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'factura_id');
    }

    /** Cuenta por cobrar contable (CxC) asociada a esta factura */
    public function contaCxc()
    {
        return $this->hasOne(ContaCxc::class, 'factura_id');
    }

    public function anuladaPor()
    {
        return $this->belongsTo(User::class, 'anulada_por');
    }

    public function scopeNoAnulada($query)
    {
        return $query->where(function ($q) {
            $q->where('anulada', false)->orWhereNull('anulada');
        });
    }
}
