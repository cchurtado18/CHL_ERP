<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'ruc',
        'telefono',
        'correo',
        'direccion',
        'notas',
        'activo',
        'created_by',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function cuentasPorPagar()
    {
        return $this->hasMany(ContaCxp::class, 'proveedor_id');
    }
}
