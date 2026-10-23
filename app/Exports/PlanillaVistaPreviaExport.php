<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlanillaVistaPreviaExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithTitle
{
    private array $data;
    private int $mes;
    private int $anio;
    private int $tipoAsociadoId;

    public function __construct(array $data,int $mes,int $anio,int $tipoAsociadoId)
    {
        $this->data = $data;
        $this->mes = $mes;
        $this->anio = $anio;
        $this->tipoAsociadoId = $tipoAsociadoId;
    }

    public function collection(): Collection
    {
        $filas = collect($this->data)
        ->values()
        ->map(function ($item, $indice) {
            $aporte = (int) data_get($item,'aporte.saldo',0);
            $prestamos = (int) collect($item['prestamos'] ?? [])->sum(function ($prestamo) {
                return (int) ($prestamo['saldo'] ?? 0);
            });

            return [
                'item' => $indice + 1,
                'institucion_id' => (int) ($item['institucion_id'] ?? 0),
                'numero_socio' => (string) ($item['numero_socio'] ?? ''),
                'documento' => (string) ($item['documento'] ?? ''),
                'nombre' =>mb_strtoupper(trim($item['nombre'] ?? ''),'UTF-8'),
                'aporte' => $aporte,
                'prestamos' => $prestamos,
                'total' => $aporte + $prestamos,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | FILA DE TOTALES
        |--------------------------------------------------------------------------
        */
        $filas->push([
            'item' => '',
            'institucion_id' => '',
            'numero_socio' => '',
            'documento' => '',
            'nombre' => 'TOTALES',
            'aporte' => (int) $filas->sum('aporte'),
            'prestamos' => (int) $filas->sum('prestamos'),
            'total' => (int) $filas->sum('total'),
        ]);

        return $filas;
    }

    public function headings(): array
    {
        return [
            'N.º',
            'Institución',
            'N.º socio',
            'Documento',
            'Nombre y apellido',
            'Aporte',
            'Préstamos',
            'Total',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $ultimaFila = count($this->data) + 2;

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:H' . max(1, $ultimaFila - 1));
        $sheet->getStyle('A1:H' . $ultimaFila)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => 'FFFFFFFF',
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FF1B55E2',
                    ],
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
            ],

            $ultimaFila => [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFE2E3E5',
                    ],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Planilla '. str_pad((string) $this->mes,2,'0',STR_PAD_LEFT). '-'. $this->anio;
    }
}
