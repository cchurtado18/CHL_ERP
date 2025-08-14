<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturacion extends Model
{
    use HasFactory;

    protected $table = 'facturacion';

    protected $fillable = [
        'cliente_id',
        'fecha_factura',
        'numero_acta',
        'monto_total',
        'moneda',
        'tasa_cambio',
        'monto_local',
        'estado_pago',
        'nota',
        'created_by',
        'updated_by',
        'delivery',
        'cantidad_paquetes',
    ];

    // Cliente relacionado
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
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
}