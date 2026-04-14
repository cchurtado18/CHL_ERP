<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncomiendaHistorialEstado extends Model
{
    use HasFactory;

    protected $table = 'encomienda_historial_estados';

    protected $fillable = [
        'encomienda_id',
        'estado',
        'comentario',
        'user_id',
        'fecha_cambio',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
