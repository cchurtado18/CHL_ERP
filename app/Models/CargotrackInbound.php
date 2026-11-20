<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CargotrackInbound extends Model
{
    protected $table = 'cargotrack_inbound';

    protected $fillable = [
        'warehouse',
        'tracking_codigo',
        'agencia_codigo',
        'consignee_nombre',
        'shipper',
        'modalidad',
        'fecha_warehouse',
        'piezas',
        'peso_lb',
        'volumen',
        'valor',
        'estado',
        'status_raw',
        'descripcion',
        'detail_url',
        'documento_path',
        'documento_mime',
        'documento_url_origen',
        'cliente_id',
        'inventario_id',
        'assign_status',
        'raw',
        'synced_at',
    ];

    protected $casts = [
        'fecha_warehouse' => 'date',
        'peso_lb' => 'decimal:2',
        'volumen' => 'decimal:4',
        'valor' => 'decimal:2',
        'raw' => 'array',
        'synced_at' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }

    public function hasDocumento(): bool
    {
        return filled($this->documento_path) && Storage::disk('local')->exists($this->documento_path);
    }
}
