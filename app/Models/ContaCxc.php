<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaCxc extends Model
{
    use HasFactory;

    protected $table = 'conta_cxc';

    protected $fillable = [
        'factura_id',
        'cliente_id',
        'fecha_emision',
        'fecha_vencimiento',
        'dias_credito',
        'monto_original',
        'saldo_actual',
        'estado_cobro',
        'dias_mora',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'monto_original' => 'decimal:2',
        'saldo_actual' => 'decimal:2',
    ];

    public function factura()
    {
        return $this->belongsTo(Facturacion::class, 'factura_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
