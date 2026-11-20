<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargotrackAgencia extends Model
{
    protected $table = 'cargotrack_agencias';

    protected $fillable = [
        'codigo',
        'nombre',
        'cliente_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public static function normalizeCodigo(string $codigo): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($codigo)) ?? '');
    }
}
