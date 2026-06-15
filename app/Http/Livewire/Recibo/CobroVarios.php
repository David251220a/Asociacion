<?php

namespace App\Http\Livewire\Recibo;

use App\Models\Banco;
use App\Models\Entidad;
use App\Models\Establecimiento;
use App\Models\FormaCobro;
use App\Models\Numeracion;
use App\Models\Persona;
use App\Models\Recibo;
use App\Models\ReciboDonacion;
use App\Models\ReciboPago;
use App\Models\ResumenAnual;
use App\Models\ResumenMensual;
use App\Models\Timbrado;
use App\Models\TipoIngreso;
use App\Models\TipoRecibo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CobroVarios extends Component
{
    public $documento;
    public $persona;
    public $ruc;
    public $formasCobro;
    public $bancos = [];
    public $cobros = [];
    public $total_abonado = 0;
    public $entidad = null;
    public $establecimiento = null;
    public $timbrado = null;
    public $tipo_ingresos;
    public $tipo_ingreso_id;
    public $donacion_monto;

    public function mount()
    {
        $this->formasCobro = FormaCobro::where('estado_id', 1)
        ->orderBy('descripcion')
        ->get();
        $this->bancos = Banco::where('estado_id', 1)
        ->where('id','<>',0)
        ->orderBy('descripcion')
        ->get();
        $this->tipo_ingresos = TipoRecibo::whereIn('id', [6])
        ->get();
        $this->tipo_ingreso_id = $this->tipo_ingresos->first()->id;
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
        $this->donacion_monto = 0;
    }


    public function render()
    {
        return view('livewire.recibo.cobro-varios');
    }

    public function buscar()
    {
        $documento = str_replace('.', '', $this->documento);
        $this->persona = Persona::where('documento', $documento)
        ->first();

        $this->ruc = $this->persona?->ruc ?? '';

        if (empty($this->persona)) {
            $this->emit('mensaje_error', 'No existe persona con este numero de documento');
            return;
        }

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
        $total_seleccionado = $this->limpiarMonto($this->donacion_monto);
        if ($total != $total_seleccionado) {
            $this->addError('total_abonado', 'El total abonado debe ser igual al monto donado.');
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

        if (!$this->persona) {
            $this->emit('mensaje_error', 'Debe seleccionar una persona valido.');
            return;
        }

        $recibo = null;

        DB::beginTransaction();

        try {

            $numeracion = Numeracion::where('timbrado_id', $this->timbrado->id)
            ->where('establecimiento_id', $this->establecimiento->id)
            ->where('modulo', 'RECIBO')
            ->lockForUpdate()
            ->first();

            $institucionId = $this->persona->asociado?->institucion_id ?? 1;

            $numeroActual = $numeracion->numero_siguiente;
            $concepto = 'INGRESOS VARIOS - DONACIONES';
            $total = $this->limpiarMonto($this->total_abonado);
            $total_seleccionado = $this->limpiarMonto($this->donacion_monto);

            $recibo = Recibo::create([
                'persona_id' => $this->persona->id,
                'tipo_recibo_id' => $this->tipo_ingreso_id,
                'sucursal' => $this->establecimiento->sucursal,
                'general' => $this->establecimiento->general,
                'numero' => $numeroActual,
                'fecha' => now(),
                'concepto' => $concepto,
                'monto_total'         => $total_seleccionado,
                'monto_abonado'       => $total,
                'monto_devuelto'      => 0,
                'estado_id'           => 1,
                'anulado'             => 0,
                'user_id'             => auth()->id(),
            ]);

            $ahora = now();
            $fechaHoy = now()->toDateString();
            $userId = auth()->id();

            ReciboDonacion::create([
                'recibo_id' => $recibo->id,
                'persona_id' => $this->persona->id,
                'fecha' => $ahora,
                'monto' => $total_seleccionado,
                'estado_id' => 1,
                'user_id' => auth()->id(),
                'usuario_modificacion' => auth()->id()
            ]);

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
                    'recibo_id'     => $recibo->id,
                    'forma_cobro_id' => $formaCobroId,
                    'banco_id'       => $bancoId,
                    'monto'          => $monto,
                    'estado_id'     => 1,
                    'created_at'     => $ahora,
                    'updated_at'     => $ahora,
                ];
            }

            if (!empty($insertCobros)) {
                ReciboPago::insert($insertCobros);
            }

            $numeracion->numero_siguiente = $numeroActual + 1;
            $numeracion->save();

            /*
            |--------------------------------------------------------------------------
            | RESUMEN MENSUAL
            |--------------------------------------------------------------------------
            */
            $fechaResumen = Carbon::parse($recibo->fecha_factura);
            $anioResumen = (int) $fechaResumen->year;
            $mesResumen  = (int) $fechaResumen->month;
            $montoIngreso = (float) $recibo->monto_total;

            $resumenMensual = ResumenMensual::where('anio', $anioResumen)
            ->where('mes', $mesResumen)
            ->where('tipo_ingreso_id', $this->tipo_ingreso_id)
            ->whereNull('tipo_egreso_id')
            ->lockForUpdate()
            ->first();

            if (!$resumenMensual) {
                $resumenMensual = ResumenMensual::create([
                    'anio'             => $anioResumen,
                    'mes'              => $mesResumen,
                    'tipo_ingreso_id'   => $this->tipo_ingreso_id,
                    'tipo_egreso_id'   => null,
                    'tipo_movimiento'  => 'I',
                    'total_ingreso'    => 0,
                    'total_egreso'     => 0,
                    'fecha_calculo'    => null,
                    'usuario_calculo'  => null,
                    'observacion'      => 'Creado automáticamente desde cobro varios - donaciones',
                ]);
            }

            $resumenMensual->total_ingreso = (float) $resumenMensual->total_ingreso + $montoIngreso;
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
                    'observacion'     => 'Creado automáticamente desde cobro varios - donaciones',
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

        return redirect()->route('recibo.show', $recibo->id)->with('message', 'Ingreso realizado correctamente.');
    }


}
