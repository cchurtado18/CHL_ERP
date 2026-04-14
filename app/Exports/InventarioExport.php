<?php

namespace App\Exports;

use App\Models\Inventario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Events\AfterSheet;

class InventarioExport implements FromCollection, WithMapping, WithStyles, WithTitle, WithEvents, WithDrawings
{
    public function collection()
    {
        return Inventario::with(['cliente', 'servicio'])->get();
    }

    public function map($row): array
    {
        return [
            $row->cliente ? $row->cliente->nombre_completo : '',
            $row->servicio ? $row->servicio->tipo_servicio : '',
            $row->peso_lb,
            $row->numero_guia, // Warehouse ahora es numero_guia
            $row->estado,
            $row->fecha_ingreso ? date('Y-m-d', strtotime($row->fecha_ingreso)) : '',
            $row->monto_calculado,
            $row->tarifa_manual,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados azul CH Logistics y negrita en la fila 3
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '1A2E75']
            ],
            'alignment' => [
                'horizontal' => 'right',
                'vertical' => 'center',
            ],
        ]);
        // Título grande en la fila 2
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 18,
                'color' => ['rgb' => '1A2E75']
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);
        // Alinear a la derecha todos los datos
        $sheet->getStyle('A4:H1000')->getAlignment()->setHorizontal('right');
        // Padding visual para todas las celdas
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        return [];
    }

    public function title(): string
    {
        return 'Inventario CH Logistics';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Logo en la fila 1 (A1:H1) y título grande en la fila 2 (A2:H2)
                $event->sheet->mergeCells('A1:H1');
                $event->sheet->mergeCells('A2:H2');
                $event->sheet->setCellValue('A2', 'Inventario de Paquetes - CH Logistics ERP');
                // Encabezados manualmente en la fila 3
                $headers = ['Cliente', 'Servicio', 'Peso', 'Warehouse', 'Estado', 'Ingreso', 'Monto', 'P. Unit.'];
                $col = 'A';
                foreach ($headers as $header) {
                    $event->sheet->setCellValue($col.'3', $header);
                    $col++;
                }
                // Ajustar ancho de columnas
                foreach (range('A', 'H') as $col) {
                    $event->sheet->getDelegate()->getColumnDimension($col)->setWidth(22);
                }
                // Altura de filas para logo, título y encabezados
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(90);
                $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(40);
                $event->sheet->getDelegate()->getRowDimension(3)->setRowHeight(30);
            }
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo CH Logistics');
        $drawing->setDescription('Logo CH Logistics');
        $drawing->setPath(public_path('logo_skylinkone.png'));
        $drawing->setHeight(120);
        $drawing->setCoordinates('E1'); // Centrado en la fila 1
        $drawing->setOffsetX(0);
        $drawing->setOffsetY(0);
        return [$drawing];
    }
} 