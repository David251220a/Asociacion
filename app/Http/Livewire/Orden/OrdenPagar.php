<?php

namespace App\Http\Livewire\Orden;

use App\Models\Banco;
use App\Models\FormaCobro;
use App\Models\OrdenPago;
use App\Models\OrdenPagoPago;
use App\Models\Prestamo;
use App\Models\ResumenAnual;
use App\Models\ResumenMensual;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OrdenPagar extends Component
{
    public $data;
    public $bancos = [];
    public $cobros = [];
    public $total_abonado = 0;
    public $formasCobro;

    public function mount(OrdenPago $ordenPago)
    {
        $this->data = $ordenPago;
        $this->formasCobro = FormaCobro::where('estado_id', 1)
        ->orderBy('descripcion')
        ->get();
        $this->bancos = Banco::where('estado_id', 1)
        ->where('id','<>',0)
        ->orderBy('descripcion')
        ->get();
        $this->cobros = [
            [
                'fecha_pago' => now()->toDateString(),
                'forma_cobro_id' => '',
                'banco_id' => '',
                'banco_ver' => 0,
                'monto' => 0,
                'numero_comprobante' => '',
            ]
        ];
    }

    public function render()
    {
        return view('livewire.orden.orden-pagar');
    }

    public function agregarCobro()
    {
        $this->cobros[] = [
            'fecha_pago' => now()->toDateString(),
            'forma_cobro_id' => '',
            'banco_id' => '',
            'banco_ver' => 0,
            'monto' => 0,
            'numero_comprobante' => '',
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

    private function limpiarMonto($monto)
    {
        $monto = trim((string) $monto);
        $monto = str_replace(['.', ',', ' '], '', $monto);
        $monto = preg_replace('/[^0-9]/', '', $monto);

        return $monto === '' ? 0 : (int) $monto;
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
            if (!empty($cobro['banco_ver']) && empty($cobro['numero_comprobante'])) {
                $this->addError("cobros.$i.numero_comprobante", 'Debe ingresar el número de comprobante.');
            }
        }

        if ($this->total_abonado != $this->data->total) {
            $this->addError('total_abonado', 'La suma del total a pagar debe ser igual al monto de la Orden de Pago.');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return false;
        }

        return true;
    }

    public function pagar()
    {
        if (!$this->validarCobros()) {
            $this->emit('mensaje_error', 'Debe corregir las formas de cobro antes de grabar.');
            return false;
        }

        $orden = OrdenPago::find($this->data->id);

        if($orden->estado_pago == 1){
            $this->emit('mensaje_error', 'La orden de pago ya se encuentra pagado.');
            return false;
        }

        DB::beginTransaction();

        try {
            $ahora = now();
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
                    'orden_pago_id'     => $orden->id,
                    'fecha_pago' => $cobro['fecha_pago'] ?? now()->toDateString(),
                    'forma_cobro_id' => $formaCobroId,
                    'banco_id'       => $bancoId,
                    'monto'          => $monto,
                    'numero_comprobante' => $cobro['numero_comprobante'] ?? '',
                    'estado_id'      => 1,
                    'observacion' => '',
                    'user_id' => auth()->id(),
                    'usuario_modificacion' => auth()->id(),
                    'created_at'     => $ahora,
                    'updated_at'     => $ahora,
                ];
            }

            if (!empty($insertCobros)) {
                OrdenPagoPago::insert($insertCobros);
            }

            $orden->update([
                'estado_pago' => 1,
                'fecha_pago' => $ahora,
            ]);

            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR PRÉSTAMO VINCULADO
            |--------------------------------------------------------------------------
            */
            $this->actualizarPrestamoPagado($orden,$ahora->toDateString());
            /*
            |--------------------------------------------------------------------------
            | Aquí continúa la actualización de los resúmenes
            |--------------------------------------------------------------------------
            */

            /*
            /*
            |--------------------------------------------------------------------------
            | RESUMEN MENSUAL - INGRESO POR APORTE PLANILLA
            |--------------------------------------------------------------------------
            */
            $fechaResumen = $ahora;
            $anioResumen  = (int) $fechaResumen->year;
            $mesResumen   = (int) $fechaResumen->month;
            $montoEgreso = (float) $orden->total;

            $resumenMensual = ResumenMensual::where('anio', $anioResumen)
            ->where('mes', $mesResumen)
            ->where('tipo_egreso_id', $orden->tipo_egreso_id)
            ->whereNull('tipo_ingreso_id')
            ->lockForUpdate()
            ->first();

            if (!$resumenMensual) {
                $resumenMensual = ResumenMensual::create([
                    'anio'             => $anioResumen,
                    'mes'              => $mesResumen,
                    'tipo_ingreso_id'   => null,
                    'tipo_egreso_id'   => $orden->tipo_egreso_id,
                    'tipo_movimiento'  => 'E',
                    'total_ingreso'    => 0,
                    'total_egreso'     => 0,
                    'fecha_calculo'    => null,
                    'usuario_calculo'  => null,
                    'observacion'      => 'Creado automáticamente desde orden de pago',
                ]);
            }

            $resumenMensual->total_egreso = (float) $resumenMensual->total_egreso + $montoEgreso;
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
                    'observacion'     => 'Creado automáticamente desde orden de pago',
                ]);
            }

            $resumenAnual->total_egreso = (float) $resumenAnual->total_egreso + $montoEgreso;
            $resumenAnual->saldo_final   = ((float) $resumenAnual->saldo_inicial + (float) $resumenAnual->total_ingreso) - (float) $resumenAnual->total_egreso;
            $resumenAnual->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->emit('mensaje_error', $e->getMessage());
            return false;
        }

        return redirect()->route('orden.show', $orden)->with('message','Orden de pago pagado correctamente.');
    }

    private function actualizarPrestamoPagado(OrdenPago $orden,string $fechaPago): void {
        /*
        |--------------------------------------------------------------------------
        | BUSCAR POR orden_pago_id
        |--------------------------------------------------------------------------
        |
        | No usamos solamente origen_id porque ese ID podría pertenecer a una
        | solicitud de ayuda social, mercadería u otro proceso.
        |
        */
        $prestamo = Prestamo::query()
        ->where('orden_pago_id', $orden->id)
        ->lockForUpdate()
        ->first();
        /*
        |--------------------------------------------------------------------------
        | LA ORDEN NO CORRESPONDE A UN PRÉSTAMO
        |--------------------------------------------------------------------------
        */
        if (!$prestamo) {
            return;
        }
        if ((int) $prestamo->estado_id !== 1) {
            throw new \Exception('El préstamo vinculado no se encuentra activo.');
        }
        /*
        |--------------------------------------------------------------------------
        | Estado 1: pendiente de desembolso
        | Estado 2: activo o desembolsado
        |--------------------------------------------------------------------------
        */
        if ((int) $prestamo->estado_prestamo_id !== 1) {
            throw new \Exception('El préstamo vinculado ya fue desembolsado anteriormente.');
        }

        $prestamo->update([
            'fecha_desembolso' => $fechaPago,
            'estado_prestamo_id' => 2,
            'usuario_modificacion' => auth()->id(),
        ]);
    }

}
