<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContaGasto extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'conta_gastos';

    protected $fillable = [
        'fecha',
        'categoria_id',
        'descripcion',
        'monto',
        'moneda',
        'tasa_cambio',
        'cuenta_pago_id',
        'referencia',
        'asiento_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'tasa_cambio' => 'decimal:4',
    ];

    public function categoria()
    {
        return $this->belongsTo(ContaGastoCategoria::class, 'categoria_id');
    }

    public function cuentaPago()
    {
        return $this->belongsTo(ContaCuenta::class, 'cuenta_pago_id');
    }

    public function asiento()
    {
        return $this->belongsTo(ContaAsiento::class, 'asiento_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
