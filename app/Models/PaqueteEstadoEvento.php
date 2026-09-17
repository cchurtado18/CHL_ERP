<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaqueteEstadoEvento extends Model
{
    use HasFactory;

    protected $table = 'paquete_estado_eventos';

    protected $fillable = [
        'inventario_id',
        'estado',
        'estado_origen',
        'ubicacion',
        'descripcion',
        'evento_at',
        'payload',
    ];

    protected $casts = [
        'evento_at' => 'datetime',
        'payload' => 'array',
    ];

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id');
    }
}
