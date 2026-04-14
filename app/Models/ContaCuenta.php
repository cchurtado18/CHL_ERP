<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaCuenta extends Model
{
    use HasFactory;

    protected $table = 'conta_cuentas';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'subtipo',
        'cuenta_padre_id',
        'acepta_movimiento',
        'activa',
    ];

    protected $casts = [
        'acepta_movimiento' => 'boolean',
        'activa' => 'boolean',
    ];

    public function padre()
    {
        return $this->belongsTo(self::class, 'cuenta_padre_id');
    }

    public function hijas()
    {
        return $this->hasMany(self::class, 'cuenta_padre_id');
    }
}
