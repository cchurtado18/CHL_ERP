<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaAsiento extends Model
{
    use HasFactory;

    protected $table = 'conta_asientos';

    protected $fillable = [
        'numero',
        'fecha',
        'periodo_anio',
        'periodo_mes',
        'referencia_tipo',
        'referencia_id',
        'descripcion',
        'moneda',
        'tasa_cambio',
        'total_debito',
        'total_credito',
        'estado',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'fecha' => 'date',
        'tasa_cambio' => 'decimal:4',
        'total_debito' => 'decimal:2',
        'total_credito' => 'decimal:2',
    ];

    public function detalles()
    {
        return $this->hasMany(ContaAsientoDetalle::class, 'asiento_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
