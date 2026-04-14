<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncomiendaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'encomienda_id',
        'tipo_item',
        'descripcion',
        'cantidad',
        'metodo_cobro',
        'peso_lb',
        'largo_in',
        'ancho_in',
        'alto_in',
        'pie_cubico',
        'tarifa_manual',
        'monto_total_item',
        'foto_paths',
        'incluye_delivery',
        'delivery_monto',
    ];

    protected $casts = [
        'foto_paths' => 'array',
        'incluye_delivery' => 'boolean',
        'delivery_monto' => 'decimal:2',
    ];

    /** @return list<string> */
    public function fotoPathsList(): array
    {
        $v = $this->foto_paths;
        if (! is_array($v)) {
            return [];
        }

        return array_values(array_filter($v, fn ($p) => is_string($p) && $p !== ''));
    }

    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class);
    }
}
