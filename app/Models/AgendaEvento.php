<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaEvento extends Model
{
    protected $table = 'agenda_eventos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'starts_at',
        'ends_at',
        'todo_el_dia',
        'ubicacion',
        'owner_id',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'todo_el_dia' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
