<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaPagoProveedor extends Model
{
    use HasFactory;

    protected $table = 'conta_pagos_proveedor';

    protected $fillable = [
        'cxp_id',
        'fecha_pago',
        'monto',
        'moneda',
        'tasa_cambio',
        'metodo',
        'cuenta_banco_caja_id',
        'referencia',
        'comprobante_path',
        'voucher_id',
        'asiento_id',
        'created_by',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
        'tasa_cambio' => 'decimal:4',
    ];

    public function cxp()
    {
        return $this->belongsTo(ContaCxp::class, 'cxp_id');
    }

    public function cuentaBancoCaja()
    {
        return $this->belongsTo(ContaCuenta::class, 'cuenta_banco_caja_id');
    }

    public function voucher()
    {
        return $this->belongsTo(ContaVoucher::class, 'voucher_id');
    }

    public function tieneComprobante(): bool
    {
        return filled($this->comprobante_path);
    }

    public function asiento()
    {
        return $this->belongsTo(ContaAsiento::class, 'asiento_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
