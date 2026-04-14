<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre_completo',
        'telefono',
        'email',
        'direccion_cliente',
        'campana',
        'origen',
        'estado_usa_origen',
        'departamento_destino',
        'municipio_destino',
        'etapa',
        'interes_servicio',
        'presupuesto_estimado',
        'proximo_contacto_at',
        'ultimo_contacto_at',
        'estado_recordatorio',
        'resultado',
        'fecha_cierre',
        'motivo_perdida',
        'motivo_perdida_clave',
        'notas',
        'created_by',
        'updated_by',
        'owner_id',
    ];

    protected $casts = [
        'presupuesto_estimado' => 'decimal:2',
        'proximo_contacto_at' => 'datetime',
        'ultimo_contacto_at' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public const ETAPAS = [
        'nuevo',
        'contactado',
        'interesado',
        'negociacion',
        'seguimiento',
        'convertido',
        'perdido',
    ];

    public const RESULTADOS = ['abierto', 'convertido', 'perdido'];

    public const MOTIVOS_PERDIDA = [
        'precio',
        'sin_respuesta',
        'competencia',
        'sin_presupuesto',
        'reprogramo_mas_adelante',
        'fuera_cobertura',
        'otros',
    ];

    public function interacciones()
    {
        return $this->hasMany(LeadInteraccion::class)->orderByDesc('fecha_interaccion');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isCerrado(): bool
    {
        return in_array($this->resultado, ['convertido', 'perdido'], true);
    }

    public function marcarComoConvertido(): void
    {
        $this->etapa = 'convertido';
        $this->resultado = 'convertido';
        $this->estado_recordatorio = 'completado';
        $this->fecha_cierre = now();
    }

    public function marcarComoPerdido(?string $motivo = null): void
    {
        $this->etapa = 'perdido';
        $this->resultado = 'perdido';
        $this->estado_recordatorio = 'completado';
        $this->fecha_cierre = now();
        if ($motivo !== null && trim($motivo) !== '') {
            $this->motivo_perdida = trim($motivo);
        }
    }

    public static function nextCodigo(): string
    {
        $last = static::query()->latest('id')->first();
        $next = ($last?->id ?? 0) + 1;

        return 'LD-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
