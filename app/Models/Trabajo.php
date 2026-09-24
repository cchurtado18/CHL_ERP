<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trabajo extends Model
{
    protected $table = 'trabajos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'cliente_id',
        'asignado_a',
        'asignado_por',
        'fecha_programada',
        'estado',
        'prioridad',
        'visto_at',
        'finalizado_at',
        'nota_estado',
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'visto_at' => 'datetime',
        'finalizado_at' => 'datetime',
    ];

    public const ESTADOS = [
        'asignado',
        'visto',
        'trabajando',
        'casi_termino',
        'finalizado',
    ];

    public const ESTADO_LABELS = [
        'asignado' => 'Asignado',
        'visto' => 'Visto',
        'trabajando' => 'Trabajando',
        'casi_termino' => 'Casi termino',
        'finalizado' => 'Finalizado',
    ];

    public const ESTADO_COLORS = [
        'asignado' => 'bg-slate-100 text-slate-800',
        'visto' => 'bg-sky-100 text-sky-800',
        'trabajando' => 'bg-amber-100 text-amber-900',
        'casi_termino' => 'bg-violet-100 text-violet-800',
        'finalizado' => 'bg-emerald-100 text-emerald-800',
    ];

    public const PRIORIDADES = [
        'urgente',
        'promedio',
        'no_urgente',
    ];

    public const PRIORIDAD_LABELS = [
        'urgente' => 'Urgente',
        'promedio' => 'Promedio',
        'no_urgente' => 'No urgente',
    ];

    public const PRIORIDAD_COLORS = [
        'urgente' => 'bg-red-100 text-red-800',
        'promedio' => 'bg-amber-100 text-amber-900',
        'no_urgente' => 'bg-slate-100 text-slate-700',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function asignador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }

    public function labelEstado(): string
    {
        return self::ESTADO_LABELS[$this->estado] ?? ucfirst(str_replace('_', ' ', (string) $this->estado));
    }

    public function colorEstado(): string
    {
        return self::ESTADO_COLORS[$this->estado] ?? 'bg-slate-100 text-slate-700';
    }

    public function labelPrioridad(): string
    {
        return self::PRIORIDAD_LABELS[$this->prioridad] ?? ucfirst(str_replace('_', ' ', (string) $this->prioridad));
    }

    public function colorPrioridad(): string
    {
        return self::PRIORIDAD_COLORS[$this->prioridad] ?? 'bg-slate-100 text-slate-700';
    }

    public function estaFinalizado(): bool
    {
        return $this->estado === 'finalizado';
    }
}
