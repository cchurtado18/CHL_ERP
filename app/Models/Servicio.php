<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'tipo_servicio',
        'descripcion',
    ];

    /**
     * Clave estable del tipo de servicio: "Pie Cúbico" → "pie_cubico", "Aéreo" → "aereo".
     * Sin tildes, en minúsculas y con espacios/guiones convertidos a "_".
     */
    public static function claveTipo(?string $tipo): string
    {
        $t = strtolower(\Illuminate\Support\Str::ascii((string) $tipo));

        return trim(preg_replace('/[^a-z0-9]+/', '_', $t), '_');
    }

    // Relación con tarifas
    public function tarifas()
    {
        return $this->hasMany(TarifaCliente::class, 'servicio_id');
    }

    // Relación con inventario
    public function inventario()
    {
        return $this->hasMany(Inventario::class, 'servicio_id');
    }
}