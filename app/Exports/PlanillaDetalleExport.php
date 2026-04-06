<?php

namespace App\Exports;

use App\Models\PlanillaDetalle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PlanillaDetalleExport implements FromCollection, WithHeadings
{
    protected $planillaId;

    public function __construct($planillaId)
    {
        $this->planillaId = $planillaId;
    }

    public function collection()
    {
        return PlanillaDetalle::query()
            ->join('asociados as a', 'a.id', '=', 'planilla_detalles.asociado_id')
            ->join('personas as p', 'p.id', '=', 'a.persona_id')
            ->where('planilla_detalles.planilla_id', $this->planillaId)
            ->select(
                'p.documento',
                'p.nombre',
                'p.apellido',
                'planilla_detalles.monto_esperado'
            )
            ->orderBy('p.documento')
            ->get()
            ->map(function ($item) {
                return [
                    'numero_socio'    => $item->documento,
                    'nombre_completo' => trim($item->nombre . ' ' . $item->apellido),
                    'monto_esperado'  => $item->monto_esperado,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Documento',
            'Nombre',
            'Monto',
        ];
    }
}
