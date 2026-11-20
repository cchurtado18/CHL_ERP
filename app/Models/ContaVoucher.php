<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaVoucher extends Model
{
    use HasFactory;

    protected $table = 'conta_vouchers';

    protected $fillable = [
        'folio',
        'tipo',
        'fecha',
        'monto',
        'moneda',
        'tercero_tipo',
        'tercero_id',
        'tercero_nombre',
        'concepto',
        'metodo',
        'cuenta_banco_caja_id',
        'referencia_tipo',
        'referencia_id',
        'referencia',
        'created_by',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function cuentaBancoCaja()
    {
        return $this->belongsTo(ContaCuenta::class, 'cuenta_banco_caja_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function esIngreso(): bool
    {
        return $this->tipo === 'ingreso';
    }

    public function etiquetaTipo(): string
    {
        return $this->esIngreso() ? 'Ingreso (cobro cliente)' : 'Egreso (pago proveedor)';
    }
}
