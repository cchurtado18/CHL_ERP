<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaCobro extends Model
{
    use HasFactory;

    protected $table = 'conta_cobros';

    protected $fillable = [
        'factura_id',
        'fecha_pago',
        'monto',
        'moneda',
        'tasa_cambio',
        'metodo',
        'cuenta_banco_caja_id',
        'referencia',
        'comision',
        'created_by',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
        'tasa_cambio' => 'decimal:4',
        'comision' => 'decimal:2',
    ];

    public function factura()
    {
        return $this->belongsTo(Facturacion::class, 'factura_id');
    }

    public function cuentaBancoCaja()
    {
        return $this->belongsTo(ContaCuenta::class, 'cuenta_banco_caja_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
