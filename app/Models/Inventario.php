<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';

    protected $fillable = [
        'cliente_id',
        'servicio_id',
        'factura_id',
        'peso_lb',
        'tarifa_manual',
        'monto_calculado',
        'fecha_ingreso',
        'estado',
        'numero_guia',
        'notas',
        'created_by',
        'updated_by',
        'tracking_codigo',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function factura()
    {
        return $this->belongsTo(Facturacion::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function estadoEventos()
    {
        return $this->hasMany(PaqueteEstadoEvento::class, 'inventario_id')->orderByDesc('evento_at');
    }
}
