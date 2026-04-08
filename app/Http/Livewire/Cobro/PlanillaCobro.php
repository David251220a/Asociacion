<?php

namespace App\Http\Livewire\Cobro;

use App\Models\Aporte;
use App\Models\Banco;
use App\Models\Entidad;
use App\Models\Establecimiento;
use App\Models\Factura;
use App\Models\FacturaAporte;
use App\Models\FacturaCobro;
use App\Models\FormaCobro;
use App\Models\Numeracion;
use App\Models\Persona;
use App\Models\Planilla;
use App\Models\PlanillaDetalle;
use App\Models\Timbrado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
    public $documento;
    public $persona = null;
    public $entidad = null;
    public $establecimiento = null;
    public $timbrado = null;

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
        ->whereNotIn('id', 0)
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

        $this->entidad = Entidad::find(1);
        $this->establecimiento = Establecimiento::find(1);
        $this->timbrado = Timbrado::find(1);
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

    public function grabar()
    {
        if (!$this->archivo) {
            $this->emit('mensaje_error', 'Debe seleccionar un archivo Excel.');
            return false;
        }

        if (!$this->verificado) {
            $this->emit('mensaje_error', 'Primero debe verificar el archivo.');
            return false;
        }

        if ((int) $this->monto_excel <= 0) {
            $this->emit('mensaje_error', 'El monto total del archivo debe ser mayor a cero.');
            return false;
        }

        if (!$this->validarCobros()) {
            $this->emit('mensaje_error', 'Debe corregir las formas de cobro antes de grabar.');
            return false;
        }

        if (!$this->persona || empty($this->persona['id'])) {
            $this->emit('mensaje_error', 'Debe seleccionar una persona válida.');
            return;
        }

        $factura = null;
        DB::beginTransaction();

        try {

            $numeracion = Numeracion::where('timbrado_id', $this->timbrado->id)
            ->where('establecimiento_id', $this->establecimiento->id)
            ->where('tipo_documento_id', 1)
            ->lockForUpdate()
            ->first();
            
            $numeroActual = $numeracion->numero_siguiente;
            $tipo = strtoupper($this->planilla->tipoAsociado->descripcion);
            $concepto = 'COBRO PLANILLA ' . $tipo . ' ' . $this->planilla->planilla_numero . '/' . $this->planilla->planilla_anio;

            $factura = Factura::create([
                'persona_id' => $this->persona['id'],
                'timbrado_id' => $this->timbrado->id,
                'establecimiento_id' => $this->establecimiento->id,
                'tipo_factura_id' => 1,
                'registro_id' => 0,
                'factura_sucursal' => $this->establecimiento->sucursal,
                'factura_general' => $this->establecimiento->general,
                'factura_numero' => $numeroActual,
                'fecha_factura' => now(),
                'tipo_documento_id' => 1,
                'tipo_transaccion_id' => $this->entidad->tipo_transaccion_id,
                'condicion_pago' => 1,
                'concepto' => $concepto,
                'monto_total'         => $this->monto_excel,
                'monto_abonado'       => $this->total_abonado,
                'monto_devuelto'      => 0,
                'estado_id'           => 1,
                'anulado'             => 0,
                'generado_sifen'      => 0,
                'user_id'             => auth()->id(),
            ]);

            $rows = Excel::toArray([], $this->archivo);

            if (empty($rows) || empty($rows[0])) {
                throw new \Exception('El archivo no contiene datos.');
            }

            $filas = collect($rows[0])->slice(1)->values();
            $datosExcel = $filas->map(function ($fila, $index) {
                return [
                    'fila'      => $index + 2,
                    'documento' => $this->limpiarDocumento($fila[0] ?? null),
                    'nombre'    => trim((string) ($fila[1] ?? '')),
                    'monto'     => $this->limpiarMonto($fila[2] ?? 0),
                ];
            })->filter(function ($item) {
                return !empty($item['documento']) && (int) $item['monto'] > 0;
            })->values();

            if ($datosExcel->isEmpty()) {
                throw new \Exception('El archivo no tiene registros válidos para grabar.');
            }

            // Traer asociados de la planilla
            $detallesPlanilla = PlanillaDetalle::query()
            ->join('asociados', 'asociados.id', '=', 'planilla_detalles.asociado_id')
            ->join('personas', 'personas.id', '=', 'asociados.persona_id')
            ->where('planilla_detalles.planilla_id', $this->planilla->id)
            ->where('planilla_detalles.estado_id', 1)
            ->select(
                'planilla_detalles.asociado_id',
                'personas.documento'
            )
            ->get()
            ->map(function ($item) {
                return [
                    'asociado_id' => $item->asociado_id,
                    'documento'   => $this->limpiarDocumento($item->documento),
                ];
            });

            $mapaPlanilla = collect($detallesPlanilla)->keyBy('documento');

            $ahora = now();
            $fechaHoy = now()->toDateString();
            $fechaAporte = $fechaAporte = Carbon::createFromDate($this->planilla->anio, $this->planilla->mes, 1)->endOfMonth()->toDateString();;
            $userId = auth()->id();

            $insertFacturaAportes = [];
            $insertAportes = [];

            foreach ($datosExcel as $item) {
                if (!isset($mapaPlanilla[$item['documento']])) {
                    continue;
                }

                $asociadoId = $mapaPlanilla[$item['documento']]['asociado_id'];
                $monto = (int) $item['monto'];

                $insertFacturaAportes[] = [
                    'asociado_id'          => $asociadoId,
                    'planilla'             => 0,
                    'planilla_numero'      => $this->planilla->planilla_numero,
                    'planilla_anio'        => $this->planilla->planilla_anio,
                    'fecha_aporte'         => $fechaAporte,
                    'mes'                  => $this->planilla->mes,
                    'anio'                 => $this->planilla->anio,
                    'aporte'               => $monto,
                    'estado_id'            => 1,
                    'user_id'              => $userId,
                    'usuario_modificacion' => $userId,
                    'created_at'           => $ahora,
                    'updated_at'           => $ahora,
                ];

                $insertAportes[] = [
                    'asociado_id'          => $asociadoId,
                    'tipo_asociado_id'     => $this->planilla->tipo_asociado_id,
                    'mes'                  => $this->planilla->mes,
                    'anio'                 => $this->planilla->anio,
                    'fecha_aporte'         => $fechaAporte,
                    'aporte'               => $monto,
                    'fecha_ingreso'        => $fechaHoy,
                    'factura_id'           => $factura->id,
                    'estado_id'            => 1,
                    'user_id'              => $userId,
                    'usuario_modificacion' => $userId,
                    'created_at'           => $ahora,
                    'updated_at'           => $ahora,
                ];
            }

            if (!empty($insertFacturaAportes)) {
                FacturaAporte::insert($insertFacturaAportes);
            }

            if (!empty($insertAportes)) {
                Aporte::insert($insertAportes);
            }

            $insertCobros = [];

            foreach ($this->cobros as $cobro) {
                $formaCobroId = $cobro['forma_cobro_id'] ?? null;
                $bancoVer = (int) ($cobro['banco_ver'] ?? 0);
                $bancoDefaultId = 1;
                $bancoId = $bancoVer === 1 ? $cobro['banco_id'] : $bancoDefaultId;
                $monto = $this->limpiarMonto($cobro['monto'] ?? 0);
                if($bancoId == ''){
                    $bancoId = 0;
                }
                if (!$formaCobroId || $monto <= 0) {
                    continue;
                }

                $insertCobros[] = [
                    'factura_id'     => $factura->id,
                    'forma_cobro_id' => $formaCobroId,
                    'banco_id'       => $bancoId,
                    'monto'          => $monto,
                    'created_at'     => $ahora,
                    'updated_at'     => $ahora,
                ];
            }

            if (!empty($insertCobros)) {
                FacturaCobro::insert($insertCobros);
            }

            $numeracion->numero_siguiente = $numeroActual + 1;
            $numeracion->save();

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->emit('mensaje_error', $e->getMessage());
            return;
        }

        return redirect()->route('factura.show', $factura->id)->with('message', 'Ingreso realizado correctamente.');
    }

    public function buscarPersona()
    {
        $this->persona = null;

        if (empty($this->documento)) {
            return;
        }

        $documentoLimpio = $this->limpiarDocumento($this->documento);

        $persona = Persona::where('documento', $documentoLimpio)->first();

        if (!$persona) {
            $this->emit('mensaje_error', 'No se encontró la persona.');
            return;
        }

        $this->persona = [
            'id' => $persona->id,
            'ruc' => $persona->ruc,
            'nombre' => $persona->nombre . ' ' . $persona->apellido,
        ];
    }

}
