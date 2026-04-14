<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encomienda extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'remitente_id',
        'destinatario_id',
        'estado_actual',
        'tipo_servicio',
        'cantidad_bultos',
        'valor_declarado',
        'descripcion_general',
        'observaciones',
        'subtotal',
        'total',
        'created_by',
        'updated_by',
    ];

    public function remitente()
    {
        return $this->belongsTo(Remitente::class);
    }

    public function destinatario()
    {
        return $this->belongsTo(Destinatario::class);
    }

    public function items()
    {
        return $this->hasMany(EncomiendaItem::class);
    }

    public function historialEstados()
    {
        return $this->hasMany(EncomiendaHistorialEstado::class);
    }

    public function factura()
    {
        return $this->hasOne(Facturacion::class);
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
