<?php

namespace App\Exports;

use App\Models\PlanillaDetalle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class PlanillaDetalleExport implements FromCollection, WithHeadings
{
    protected $planillaId;

    public function __construct($planillaId)
    {
        $this->planillaId = $planillaId;
    }

    public function collection()
    {
        $otrosPorDetalle = DB::table('otro_descuento_cuotas')
            ->select('planilla_detalle_id', DB::raw('SUM(monto_esperado) as monto_otros'))
            ->where('estado_id', 1)
            ->groupBy('planilla_detalle_id');

        return PlanillaDetalle::query()
            ->join('asociados as a', 'a.id', '=', 'planilla_detalles.asociado_id')
            ->join('personas as p', 'p.id', '=', 'a.persona_id')
            ->leftJoinSub($otrosPorDetalle, 'od', function ($join) {
                $join->on('od.planilla_detalle_id', '=', 'planilla_detalles.id');
            })
            ->where('planilla_detalles.planilla_id', $this->planillaId)
            ->select(
                'p.documento',
                'p.nombre',
                'p.apellido',
                'planilla_detalles.monto_esperado',
                DB::raw('COALESCE(od.monto_otros, 0) as monto_otros')
            )
            ->orderBy('p.documento')
            ->get()
            ->map(function ($item) {
                return [
                    'numero_socio'    => $item->documento,
                    'nombre_completo' => trim($item->nombre . ' ' . $item->apellido),
                    'monto_esperado'  => $item->monto_esperado,
                    'otros_descuentos' => $item->monto_otros,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Documento',
            'Nombre',
            'Monto',
            'Otros descuentos',
        ];
    }
}
