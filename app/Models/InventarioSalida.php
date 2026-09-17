<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InventarioSalida extends Model
{
    protected $table = 'inventario_salidas';

    protected $fillable = [
        'descripcion',
        'sucursal_origen',
        'sucursal_destino',
        'created_by',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paquetes(): BelongsToMany
    {
        return $this->belongsToMany(Inventario::class, 'inventario_salida_paquete', 'inventario_salida_id', 'inventario_id')
            ->withTimestamps();
    }
}
