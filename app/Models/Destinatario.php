<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destinatario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_completo',
        'telefono_1',
        'telefono_2',
        'direccion',
        'referencias',
        'ciudad',
        'departamento',
        'cedula',
        'autorizado_para_recibir',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'autorizado_para_recibir' => 'boolean',
    ];

    public function encomiendas()
    {
        return $this->hasMany(Encomienda::class);
    }
}
