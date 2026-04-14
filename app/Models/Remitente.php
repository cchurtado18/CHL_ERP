<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remitente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_completo',
        'telefono',
        'correo',
        'direccion',
        'ciudad',
        'estado',
        'identificacion',
        'created_by',
        'updated_by',
    ];

    public function encomiendas()
    {
        return $this->hasMany(Encomienda::class);
    }
}
