<?php

namespace App\Exports;

use App\Models\Inventario;
use App\Models\InventarioSalida;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class InventarioSalidaPaquetesExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    private ?InventarioSalida $salida = null;

    private ?Collection $paquetes = null;

    public function __construct(private readonly int $inventarioSalidaId)
    {
        $this->salida = InventarioSalida::query()->find($this->inventarioSalidaId);
    }

    public function collection(): Collection
    {
        if ($this->paquetes !== null) {
            return $this->paquetes;
        }

        if (! $this->salida) {
            return $this->paquetes = collect();
        }

        $ids = DB::table('inventario_salida_paquete')
            ->where('inventario_salida_id', $this->salida->id)
            ->pluck('inventario_id');

        return $this->paquetes = Inventario::query()
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $paquetes = $this->collection();
                $row = $sheet->getHighestRow() + 2;

                $sheet->setCellValue('A'.$row, 'Cantidad de paquetes');
                $sheet->setCellValue('B'.$row, $paquetes->count());
                $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);

                $row++;
                $sheet->setCellValue('A'.$row, 'Total peso (lb)');
                $sheet->setCellValue('B'.$row, (float) $paquetes->sum(fn ($p) => (float) ($p->peso_lb ?? 0)));
                $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
            },
        ];
    }
}
