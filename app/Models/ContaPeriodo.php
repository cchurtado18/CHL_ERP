<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaPeriodo extends Model
{
    use HasFactory;

    protected $table = 'conta_periodos';

    protected $fillable = [
        'anio',
        'mes',
        'estado',
        'fecha_cierre',
        'cerrado_por',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
    ];

    public function cerrador()
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }
}
