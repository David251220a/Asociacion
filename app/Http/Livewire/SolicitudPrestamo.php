<?php

namespace App\Http\Livewire;

use App\Models\Numeraciones;
use App\Models\SolicitudConfig;
use App\Models\SolicitudPrestamo as ModelsSolicitudPrestamo;
use App\Models\SolicitudPrestamoDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SolicitudPrestamo extends Component
{
    public $persona;

    public $config;

    public array $montosDisponibles = [];

    public array $plazosDisponibles = [];

    public int $montoSeleccionado = 0;

    public int $cantidadCuotas = 0;

    public int $montoInteres = 0;

    public int $montoTotal = 0;

    public int $montoCuota = 0;

    public string $tasaAplicada = '0';

    public array $detalleCuotas = [];

    public function mount(): void
    {
        $this->persona = auth()->user()->persona;

        if (!$this->persona) {
            abort(403, 'No se encontró la información del asociado.');
        }

        $this->config = SolicitudConfig::find(2);

        $this->generarMontosDisponibles();
        $this->generarPlazosDisponibles();
    }

    private function generarMontosDisponibles(): void
    {
        $montoMinimo = (int) $this->config->monto_minimo;
        $montoMaximo = (int) $this->config->monto_maximo;
        if ($montoMinimo <= 0 || $montoMaximo < $montoMinimo) {
            return;
        }

        for ($monto = $montoMinimo; $monto <= $montoMaximo; $monto += 100000) {
            $this->montosDisponibles[] = $monto;
        }
    }

    private function generarPlazosDisponibles(): void
    {
        $plazoMinimo = (int) $this->config->plazo_minimo;
        $plazoMaximo = (int) $this->config->plazo_maximo;

        /*
         * Por ahora solamente se permiten solicitudes
         * de una o dos cuotas.
        */
        foreach ([1, 2] as $plazo) {
            if ($plazo >= $plazoMinimo && $plazo <= $plazoMaximo) {
                $this->plazosDisponibles[] = $plazo;
            }
        }
    }

    public function updatedMontoSeleccionado(): void
    {
        $this->calcularPrestamo();
    }

    public function updatedCantidadCuotas(): void
    {
        $this->calcularPrestamo();
    }

    private function calcularPrestamo(): void
    {
        $this->montoInteres = 0;
        $this->montoTotal = 0;
        $this->montoCuota = 0;
        $this->tasaAplicada = '0';
        $this->detalleCuotas = [];

        if ($this->montoSeleccionado <= 0 ||$this->cantidadCuotas <= 0) {
            return;
        }

        if (!in_array($this->montoSeleccionado,$this->montosDisponibles,true)) {
            return;
        }

        if (!in_array($this->cantidadCuotas,$this->plazosDisponibles,true)) {
            return;
        }

        $tasa = $this->cantidadCuotas === 1 ? $this->config->tasa_cuota_unica : $this->config->tasa_cuota_mensual / $this->cantidadCuotas;
        $this->tasaAplicada = number_format( (float) $tasa, 2, '.', '');
        $tasaEnCentesimas = (int) round((float) $tasa * 100);
        $interesPorPeriodo = intdiv(($this->montoSeleccionado * $tasaEnCentesimas) + 5000,10000);
        $this->montoInteres = $this->cantidadCuotas === 1 ? $interesPorPeriodo : $interesPorPeriodo * $this->cantidadCuotas;
        $this->montoTotal = $this->montoSeleccionado + $this->montoInteres;
        $this->montoCuota = intdiv($this->montoTotal, $this->cantidadCuotas);

        $this->generarDetalleCuotas();
    }

    private function generarDetalleCuotas(): void
    {
        $capitalBase = intdiv($this->montoSeleccionado,$this->cantidadCuotas);
        $interesBase = intdiv($this->montoInteres,$this->cantidadCuotas);
        $capitalAcumulado = 0;

        $interesAcumulado = 0;

        for ($numeroCuota = 1; $numeroCuota <= $this->cantidadCuotas; $numeroCuota++) {
            $ultimaCuota = $numeroCuota === $this->cantidadCuotas;
            $capital = $ultimaCuota ? $this->montoSeleccionado - $capitalAcumulado : $capitalBase;
            $interes = $ultimaCuota ? $this->montoInteres - $interesAcumulado : $interesBase;
            $totalCuota = $capital + $interes;
            $this->detalleCuotas[] = [
                'numero_cuota' => $numeroCuota,
                'fecha_vencimiento' => now()->addMonthsNoOverflow($numeroCuota)->endOfMonth()->format('d/m/Y'),
                'monto_capital' => $capital,
                'monto_interes' => $interes,
                'iva' => 0,
                'monto_cuota' => $totalCuota,
                'monto_total' => $totalCuota,
            ];
            $capitalAcumulado += $capital;
            $interesAcumulado += $interes;
        }
    }

    public function render()
    {
        return view('livewire.solicitud-prestamo');
    }

    public function guardar()
    {
        $validator = Validator::make([
            'montoSeleccionado' => $this->montoSeleccionado,
            'cantidadCuotas' => $this->cantidadCuotas,
        ], [
            'montoSeleccionado' => ['required','integer',Rule::in($this->montosDisponibles)],
            'cantidadCuotas' => ['required','integer',Rule::in($this->plazosDisponibles)],
        ], ['montoSeleccionado.required' => 'Debe seleccionar el monto del préstamo.',
            'montoSeleccionado.in' => 'El monto seleccionado no se encuentra disponible.',
            'cantidadCuotas.required' => 'Debe seleccionar la cantidad de cuotas.',
            'cantidadCuotas.in' => 'La cantidad de cuotas seleccionada no se encuentra disponible.',
        ]);

        if ($validator->fails()) {
            $this->addError(
                'prestamo',
                $validator->errors()->first()
            );
            return;
        }
        $this->resetErrorBag('prestamo');
        $datos = $validator->validated();
        $this->calcularPrestamo();
        // Continuar con la grabación.
        DB::beginTransaction();
        try {
            $anio = now()->year;

            $numeracion = Numeraciones::where('tipo', 6)
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

            if (!$numeracion) {
                $numero = 1;
                Numeraciones::create([
                    'tipo' => 6,
                    'anio' => $anio,
                    'descripcion' => 'Solicitud de préstamo',
                    'numero' => 2
                ]);
            } else {
                $numero = $numeracion->numero;
                $numeracion->numero = $numero + 1;
                $numeracion->save();
            }

            /*
            |--------------------------------------------------------------------------
            | CABECERA DE LA SOLICITUD
            |--------------------------------------------------------------------------
            */
            $solicitud = ModelsSolicitudPrestamo::create([
                'persona_id' => $this->persona->id,
                'estado_solicitud_id' => 1,
                'tipo_prestamo_id' => 1,
                'fecha_solicitud' => now()->toDateString(),
                'anio' => $anio,
                'numero_solicitud' => $numero,
                'monto_solicitado' => $datos['montoSeleccionado'],
                'monto_aprobado' => null,
                'tasa_aplicada' => $this->tasaAplicada,
                'cantidad_cuotas' => $datos['cantidadCuotas'],
                'orden_pago_id' => null,
                'observaciones' => null,
                'fecha_aprobacion_rechazo' => null,
                'usuario_aprobacion_rechazo_id' => null,
                'motivo_rechazo' => null,
                'estado_id' => 1,
                'usuario_id' => auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | DETALLE DE CUOTAS
            |--------------------------------------------------------------------------
            */
            foreach ($this->detalleCuotas as $cuota) {
                SolicitudPrestamoDetalle::create([
                    'solicitud_prestamo_id' => $solicitud->id,
                    'numero_cuota' => $cuota['numero_cuota'],
                    'fecha_vencimiento' => Carbon::createFromFormat('d/m/Y', $cuota['fecha_vencimiento'])->toDateString(),
                    'monto_capital' => $cuota['monto_capital'],
                    'monto_interes' => $cuota['monto_interes'],
                    'monto_cuota' => $cuota['monto_cuota'],
                    'iva' => $cuota['iva'],
                    'monto_total' => $cuota['monto_total'],
                ]);
            }

            DB::commit();
            return redirect()
            ->route('solicitudes')
            ->with(
                'message',
                'Su solicitud de préstamo N.º '
                . str_pad($numero, 5, '0', STR_PAD_LEFT)
                . '/'
                . $anio
                . ' fue registrada correctamente.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->addError('prestamo', $e->getMessage());
        }
    }
}
