<?php

namespace App\Http\Livewire\Cobro;

use App\Models\Banco;
use App\Models\FormaCobro;
use App\Models\Planilla;
use App\Models\PlanillaDetalle;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class PlanillaCobro extends Component
{

    use WithFileUploads;

    public $meses, $mes;
    public $planilla;
    public $archivo;
    public $cantidad_excel = 0;
    public $monto_excel = 0;
    public $erroresDocumentos = [];
    public $verificado = false;
    public $formasCobro = [];
    public $bancos = [];
    public $cobros = [];
    public $total_abonado = 0;

    public function mount(Planilla $planilla)
    {
        $this->planilla;       
        $this->meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        $this->mes = $this->meses[$this->planilla->mes]; 
        $this->formasCobro = FormaCobro::where('estado_id', 1)
        ->orderBy('descripcion')
        ->get();
        $this->bancos = Banco::where('estado_id', 1)
        ->orderBy('descripcion')
        ->get();
        $this->cobros = [
            [
                'forma_cobro_id' => '',
                'banco_id' => '',
                'banco_ver' => 0,
                'monto' => 0,
            ]
        ];
    }

    public function verificar()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ], [
            'archivo.required' => 'Debe seleccionar un archivo Excel.',
            'archivo.mimes' => 'El archivo debe ser Excel (.xlsx o .xls).',
        ]);

        $this->resetErrorBag();
        $this->erroresDocumentos = [];
        $this->cantidad_excel = 0;
        $this->monto_excel = 0;
        $this->verificado = false;

        $rows = Excel::toArray([], $this->archivo);

        if (empty($rows) || empty($rows[0])) {
            $this->addError('archivo', 'El archivo no contiene datos.');
            return;
        }

        $filas = collect($rows[0])->slice(1)->values();

        $datosExcel = $filas->map(function ($fila, $index) {
            return [
                'fila'       => $index + 2,
                'documento'  => $this->limpiarDocumento($fila[0] ?? null),
                'nombre'     => trim((string) ($fila[1] ?? '')),
                'monto'      => $this->limpiarMonto($fila[2] ?? 0),
            ];
        })->filter(function ($item) {
            return !empty($item['documento']);
        })->values();

        if ($datosExcel->isEmpty()) {
            $this->addError('archivo', 'El archivo no tiene registros válidos.');
            return;
        }

        $detallesPlanilla = PlanillaDetalle::query()
        ->join('asociados', 'asociados.id', '=', 'planilla_detalles.asociado_id')
        ->join('personas', 'personas.id', '=', 'asociados.persona_id')
        ->where('planilla_detalles.planilla_id', $this->planilla->id)
        ->where('planilla_detalles.estado_id', 1)
        ->select(
            'planilla_detalles.id',
            'planilla_detalles.asociado_id',
            'planilla_detalles.saldo',
            'personas.documento',
            'personas.nombre',
            'personas.apellido'
        )
        ->get()
        ->map(function ($item) {
            $item->documento_limpio = $this->limpiarDocumento($item->documento ?? '');
            return $item;
        });

        $mapaPlanilla = $detallesPlanilla->keyBy('documento_limpio');

        foreach ($datosExcel as $item) {
            if (!isset($mapaPlanilla[$item['documento']])) {
                $this->erroresDocumentos[] = [
                    'fila'      => $item['fila'],
                    'documento' => $item['documento'],
                    'nombre'    => $item['nombre'],
                    'monto'     => $item['monto'],
                    'mensaje'   => 'No existe en la planilla',
                ];
            }
        }

        if (count($this->erroresDocumentos) > 0) {
            return;
        }

        $this->cantidad_excel = $datosExcel->count();
        $this->monto_excel = $datosExcel->sum('monto');
        $this->verificado = true;
        $this->emit('mensaje_exitoso', 'Verificado con exito.');
    }

    private function limpiarDocumento($documento)
    {
        return preg_replace('/[^0-9]/', '', trim((string) $documento));
    }

    private function limpiarMonto($monto)
    {
        $monto = trim((string) $monto);
        $monto = str_replace(['.', ',', ' '], '', $monto);
        $monto = preg_replace('/[^0-9]/', '', $monto);

        return $monto === '' ? 0 : (int) $monto;
    }

    public function render()
    {
        return view('livewire.cobro.planilla-cobro');
    }

    public function cancelar()
    {
        $this->reset([
            'archivo',
            'cantidad_excel',
            'monto_excel',
            'erroresDocumentos',
            'verificado',
        ]);

        $this->cobros = [
            [
                'forma_cobro_id' => '',
                'banco_id' => '',
                'banco_ver' => 0,
                'monto' => 0,
            ]
        ];

        $this->resetErrorBag();
    }

    public function agregarCobro()
    {
        $this->cobros[] = [
            'forma_cobro_id' => '',
            'banco_id' => '',
            'banco_ver' => 0,
            'monto' => 0,
        ];
    }

    public function quitarCobro($index)
    {
        unset($this->cobros[$index]);
        $this->cobros = array_values($this->cobros);
        $this->recalcularTotal();
    }

    public function cambioFormaCobro($formaCobroId, $index)
    {
        $forma = $this->formasCobro->firstWhere('id', (int) $formaCobroId);

        $this->cobros[$index]['forma_cobro_id'] = $formaCobroId;
        $this->cobros[$index]['banco_ver'] = $forma ? (int) $forma->banco_ver : 0;

        if (!$forma || (int) $forma->banco_ver === 0) {
            $this->cobros[$index]['banco_id'] = '';
        }
    }

    public function recalcularTotal()
    {
        // foreach ($this->cobros as $i => $cobro) {
        //     $this->cobros[$i]['monto'] = $this->limpiarMonto($cobro['monto'] ?? 0);
        // }

        $this->total_abonado = collect($this->cobros)->sum(function ($item) {
            return $this->limpiarMonto($item['monto'] ?? 0);
        });
    }

    protected function validarCobros()
    {
        $this->validate([
            'cobros' => 'required|array|min:1',
            'cobros.*.forma_cobro_id' => 'required|exists:forma_cobros,id',
            'cobros.*.monto' => 'required|numeric|min:1',
        ], [
            'cobros.required' => 'Debe ingresar al menos una forma de cobro.',
            'cobros.*.forma_cobro_id.required' => 'Debe seleccionar una forma de cobro.',
            'cobros.*.monto.required' => 'Debe ingresar el monto.',
        ]);

        foreach ($this->cobros as $i => $cobro) {
            if (!empty($cobro['banco_ver']) && empty($cobro['banco_id'])) {
                $this->addError("cobros.$i.banco_id", 'Debe seleccionar un banco.');
            }
        }

        if ($this->total_abonado != $this->monto_excel) {
            $this->addError('total_abonado', 'El total abonado debe ser igual al monto del Excel.');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return false;
        }

        return true;
    }
}
