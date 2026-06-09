<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaGastoCategoria extends Model
{
    use HasFactory;

    protected $table = 'conta_gasto_categorias';

    protected $fillable = [
        'nombre',
        'slug',
        'cuenta_contable_id',
        'icono',
        'orden',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'orden' => 'integer',
    ];

    public function cuentaContable()
    {
        return $this->belongsTo(ContaCuenta::class, 'cuenta_contable_id');
    }

    public function gastos()
    {
        return $this->hasMany(ContaGasto::class, 'categoria_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
