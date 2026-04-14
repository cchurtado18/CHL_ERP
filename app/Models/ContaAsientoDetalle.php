<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaAsientoDetalle extends Model
{
    use HasFactory;

    protected $table = 'conta_asiento_detalles';

    protected $fillable = [
        'asiento_id',
        'cuenta_id',
        'tercero_id',
        'tercero_tipo',
        'debito',
        'credito',
        'monto_origen',
        'monto_funcional',
        'glosa',
    ];

    protected $casts = [
        'debito' => 'decimal:2',
        'credito' => 'decimal:2',
        'monto_origen' => 'decimal:2',
        'monto_funcional' => 'decimal:2',
    ];

    public function asiento()
    {
        return $this->belongsTo(ContaAsiento::class, 'asiento_id');
    }

    public function cuenta()
    {
        return $this->belongsTo(ContaCuenta::class, 'cuenta_id');
    }
}
