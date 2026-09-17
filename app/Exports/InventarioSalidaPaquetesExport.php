<?php

namespace App\Exports;

use App\Models\Inventario;
use App\Models\InventarioSalida;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventarioSalidaPaquetesExport implements FromCollection, WithHeadings, WithMapping
{
    private ?InventarioSalida $salida = null;

    public function __construct(private readonly int $inventarioSalidaId)
    {
        $this->salida = InventarioSalida::query()->find($this->inventarioSalidaId);
    }

    public function collection(): Collection
    {
        if (! $this->salida) {
            return collect();
        }

        $ids = DB::table('inventario_salida_paquete')
            ->where('inventario_salida_id', $this->salida->id)
            ->pluck('inventario_id');

        return Inventario::query()
            ->whereIn('id', $ids)
            ->with(['cliente', 'servicio', 'factura'])
            ->orderByDesc('fecha_ingreso')
            ->orderByDesc('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Salida ID',
            'Descripcion salida',
            'Sucursal origen',
            'Sucursal destino',
            'Fecha registro salida',
            'ID Paquete',
            'Numero de guia',
            'Tracking',
            'Cliente',
            'Servicio',
            'Peso (lb)',
            'Volumen (ft3)',
            'Tarifa manual',
            'Monto calculado',
            'Estado',
            'Fecha ingreso paquete',
            'Factura folio',
            'Factura id interno',
            'Notas',
        ];
    }

    public function map($row): array
    {
        $s = $this->salida;

        return [
            $s?->id,
            $s?->descripcion,
            $s?->sucursal_origen,
            $s?->sucursal_destino,
            $s?->created_at?->format('Y-m-d H:i:s'),
            $row->id,
            $row->numero_guia,
            $row->tracking_codigo,
            $row->cliente?->nombre_completo,
            $row->servicio?->tipo_servicio,
            $row->peso_lb,
            $row->volumen_pie3,
            $row->tarifa_manual,
            $row->monto_calculado,
            $row->estado,
            $row->fecha_ingreso ? date('Y-m-d H:i:s', strtotime((string) $row->fecha_ingreso)) : null,
            $row->factura?->etiquetaFolio(),
            $row->factura_id,
            $row->notas,
        ];
    }
}
