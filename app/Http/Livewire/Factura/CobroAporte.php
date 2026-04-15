<?php

namespace App\Http\Livewire\Factura;

use App\Models\Aporte;
use App\Models\Asociado;
use App\Models\Banco;
use App\Models\Entidad;
use App\Models\Establecimiento;
use App\Models\Factura;
use App\Models\FacturaAporte;
use App\Models\FacturaCobro;
use App\Models\FormaCobro;
use App\Models\Numeracion;
use App\Models\ResumenAnual;
use App\Models\ResumenMensual;
use App\Models\Timbrado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CobroAporte extends Component
{
    public $documento;
    public $asociado;
    public $ruc;
    public $aportesPendientes = [];
    public $seleccionados = [];
    public $totalSeleccionado = 0;
    public $formasCobro = [];
    public $bancos = [];
    public $cobros = [];
    public $total_abonado = 0;
    public $entidad = null;
    public $establecimiento = null;
    public $timbrado = null;

    public function mount()
    {
        $this->formasCobro = FormaCobro::where('estado_id', 1)
        ->orderBy('descripcion')
        ->get();
        $this->bancos = Banco::where('estado_id', 1)
        ->where('id','<>',0)
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

    public function buscar()
    {
        $documento = str_replace('.', '', $this->documento);
        $this->asociado = Asociado::with('persona')
        ->whereRelation('persona', 'documento', $documento)
        ->first();

        $this->ruc = $this->asociado?->persona?->ruc ?? '';

        if(empty($this->asociado)){
            $this->emit('mensaje_error', 'No existe asociado con este numero de documento');
        }

        if (empty($this->asociado)) {
            $this->aportesPendientes = [];
            $this->emit('mensaje_error', 'No existe asociado con este numero de documento');
            return;
        }

        $this->cargarAportesPendientes();

    }

    public function render()
    {
        return view('livewire.factura.cobro-aporte');
    }

    public function cargarAportesPendientes()
    {
        $this->aportesPendientes = [];
        $this->seleccionados = [];
        $this->totalSeleccionado = 0;

        if (!$this->asociado) {
            return;
        }

        $ultimoAporte = Aporte::where('asociado_id', $this->asociado->id)
            ->where('estado_id', 1)
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->first();

        $cantidadAportes = Aporte::where('asociado_id', $this->asociado->id)
            ->where('estado_id', 1)
            ->count();

        if ($ultimoAporte) {
            $fechaInicio = \Carbon\Carbon::create($ultimoAporte->anio, $ultimoAporte->mes, 1)->addMonth();
        } else {
            $fechaInicio = \Carbon\Carbon::create($this->asociado->anio_aporte, $this->asociado->mes_aporte, 1);
        }

        $datos = [];

        for ($i = 0; $i < 12; $i++) {
            $fecha = $fechaInicio->copy()->addMonths($i);

            $numeroPeriodo = $cantidadAportes + $i + 1;
            $monto = $numeroPeriodo <= 5 ? 30000 : 20000;

            $datos[] = [
                'id' => $i,
                'mes' => $fecha->month,
                'anio' => $fecha->year,
                'mes_nombre' => ucfirst($fecha->locale('es')->translatedFormat('F')),
                'numero_periodo' => $numeroPeriodo,
                'monto' => $monto,
            ];
        }

        $this->aportesPendientes = $datos;
    }

    public function updatedSeleccionados()
    {
        $this->totalSeleccionado = collect($this->aportesPendientes)
        ->filter(function ($item) {
            return in_array($item['id'], $this->seleccionados);
        })
        ->sum('monto');
    }

    private function limpiarMonto($monto)
    {
        $monto = trim((string) $monto);
        $monto = str_replace(['.', ',', ' '], '', $monto);
        $monto = preg_replace('/[^0-9]/', '', $monto);

        return $monto === '' ? 0 : (int) $monto;
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
            if (empty($cobro['forma_cobro_id'])) {
                $this->addError("cobros.$i.forma_cobro_id", 'Debe seleccionar una forma de cobro.');
            }

            $monto = $this->limpiarMonto($cobro['monto'] ?? 0);

            if ($monto <= 0) {
                $this->addError("cobros.$i.monto", 'Debe ingresar un monto válido.');
            }

            if (!empty($cobro['banco_ver']) && empty($cobro['banco_id'])) {
                $this->addError("cobros.$i.banco_id", 'Debe seleccionar un banco.');
            }
        }

        $total = $this->limpiarMonto($this->total_abonado);
        $total_seleccionado = $this->limpiarMonto($this->totalSeleccionado);
        if ($total != $total_seleccionado) {
            $this->addError('total_abonado', 'El total abonado debe ser igual al monto seleccionado.');
        }

        if (count($this->seleccionados) === 0) {
            $this->addError('seleccionados', 'Debe seleccionar al menos un aporte.');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return false;
        }

        return true;
    }

    public function grabar()
    {
        
        if (!$this->validarCobros()) {
            $this->emit('mensaje_error', 'Debe corregir las formas de cobro antes de grabar.');
            return false;
        }

        if (!$this->asociado) {
            $this->emit('mensaje_error', 'Debe seleccionar una asociado valido.');
            return;
        }

        $aportesSeleccionados = collect($this->aportesPendientes)
        ->filter(fn($item) => in_array($item['id'], $this->seleccionados))
        ->values();

        if ($aportesSeleccionados->isEmpty()) {
            throw new \Exception('Debe seleccionar al menos un aporte.');
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
            $concepto = 'COBRO APORTE INDIVUAL';
            $total = $this->limpiarMonto($this->total_abonado);
            $total_seleccionado = $this->limpiarMonto($this->totalSeleccionado);

            $factura = Factura::create([
                'persona_id' => $this->asociado->persona->id,
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
                'cuota' => 0,
                'concepto' => $concepto,
                'monto_total'         => $total_seleccionado,
                'monto_abonado'       => $total,
                'monto_devuelto'      => 0,
                'estado_id'           => 1,
                'anulado'             => 0,
                'generado_sifen'      => 0,
                'user_id'             => auth()->id(),
            ]);

            $ahora = now();
            $fechaHoy = now()->toDateString();
            $userId = auth()->id();

            $insertFacturaAportes = [];
            $insertAportes = [];

            foreach ($aportesSeleccionados as $item) {
                $fechaAporte = Carbon::createFromDate($item['anio'], $item['mes'], 1)
                    ->endOfMonth()
                    ->toDateString();

                $insertFacturaAportes[] = [
                    'factura_id'           => $factura->id,
                    'asociado_id'          => $this->asociado->id,
                    'planilla'             => 1, // 1 = INDIVIDUAL
                    'planilla_numero'      => 0,
                    'planilla_anio'        => 0,
                    'fecha_aporte'         => $fechaAporte,
                    'mes'                  => $item['mes'],
                    'anio'                 => $item['anio'],
                    'aporte'               => $item['monto'],
                    'estado_id'            => 1,
                    'user_id'              => $userId,
                    'usuario_modificacion' => $userId,
                    'created_at'           => $ahora,
                    'updated_at'           => $ahora,
                ];

                $insertAportes[] = [
                    'asociado_id'          => $this->asociado->id,
                    'tipo_asociado_id'     => $this->asociado->tipo_asociado_id,
                    'mes'                  => $item['mes'],
                    'anio'                 => $item['anio'],
                    'fecha_aporte'         => $fechaAporte,
                    'aporte'               => $item['monto'],
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
                    'factura_id'     => 1,
                    'created_at'     => $ahora,
                    'updated_at'     => $ahora,
                ];
            }

            if (!empty($insertCobros)) {
                FacturaCobro::insert($insertCobros);
            }

            $numeracion->numero_siguiente = $numeroActual + 1;
            $numeracion->save();

            /*
            |--------------------------------------------------------------------------
            | RESUMEN MENSUAL
            |--------------------------------------------------------------------------
            */
            $fechaResumen = Carbon::parse($factura->fecha_factura);
            $anioResumen = (int) $fechaResumen->year;
            $mesResumen  = (int) $fechaResumen->month;
            $montoIngreso = (float) $factura->monto_total;

            $resumenMensual = ResumenMensual::where('anio', $anioResumen)
            ->where('mes', $mesResumen)
            ->lockForUpdate()
            ->first();

            if (!$resumenMensual) {
                $saldoAnterior = 0;

                $mesAnterior = $mesResumen - 1;
                $anioAnterior = $anioResumen;

                if ($mesAnterior <= 0) {
                    $mesAnterior = 12;
                    $anioAnterior = $anioResumen - 1;
                }

                $resumenMesAnterior = ResumenMensual::where('anio', $anioAnterior)
                ->where('mes', $mesAnterior)
                ->first();

                if ($resumenMesAnterior) {
                    $saldoAnterior = (float) $resumenMesAnterior->saldo_final;
                }

                $resumenMensual = ResumenMensual::create([
                    'anio'            => $anioResumen,
                    'mes'             => $mesResumen,
                    'total_ingreso'   => 0,
                    'total_egreso'    => 0,
                    'saldo_final'     => $saldoAnterior,
                    'fecha_calculo'   => null,
                    'usuario_calculo' => null,
                    'observacion'     => 'Creado automáticamente desde cobro de planilla',
                ]);
            }

            $resumenMensual->total_ingreso = (float) $resumenMensual->total_ingreso + $montoIngreso;
            $resumenMensual->saldo_final   = (float) $resumenMensual->saldo_final + $montoIngreso;
            $resumenMensual->save();

            /*
            |--------------------------------------------------------------------------
            | RESUMEN ANUAL
            |--------------------------------------------------------------------------
            */
            $resumenAnual = ResumenAnual::where('anio', $anioResumen)
            ->lockForUpdate()
            ->first();

            if (!$resumenAnual) {
                $saldoInicialAnual = 0;

                $anualAnterior = ResumenAnual::where('anio', $anioResumen - 1)->first();
                if ($anualAnterior) {
                    $saldoInicialAnual = (float) $anualAnterior->saldo_final;
                }

                $resumenAnual = ResumenAnual::create([
                    'anio'            => $anioResumen,
                    'saldo_inicial'   => $saldoInicialAnual,
                    'total_ingreso'   => 0,
                    'total_egreso'    => 0,
                    'saldo_final'     => $saldoInicialAnual,
                    'fecha_calculo'   => null,
                    'usuario_calculo' => null,
                    'observacion'     => 'Creado automáticamente desde cobro de planilla',
                ]);
            }

            $resumenAnual->total_ingreso = (float) $resumenAnual->total_ingreso + $montoIngreso;
            $resumenAnual->saldo_final   = ((float) $resumenAnual->saldo_inicial + (float) $resumenAnual->total_ingreso) - (float) $resumenAnual->total_egreso;
            $resumenAnual->save();

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->emit('mensaje_error', $e->getMessage());
            return;
        }

        return redirect()->route('factura.show', $factura->id)->with('message', 'Ingreso realizado correctamente.');
    }
}
