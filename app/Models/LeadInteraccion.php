<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadInteraccion extends Model
{
    use HasFactory;

    protected $table = 'lead_interacciones';

    protected $fillable = [
        'lead_id',
        'tipo',
        'detalle',
        'fecha_interaccion',
        'created_by',
    ];

    protected $casts = [
        'fecha_interaccion' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
