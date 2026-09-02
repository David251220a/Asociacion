<?php

namespace App\Http\Livewire\Cobro;

use App\Models\Aporte;
use App\Models\Banco;
use App\Models\Entidad;
use App\Models\Establecimiento;
use App\Models\FormaCobro;
use App\Models\Numeracion;
use App\Models\Persona;
use App\Models\Planilla;
use App\Models\PlanillaAporte;
use App\Models\PlanillaDetalle;
use App\Models\PlanillaPrestamo;
use App\Models\Prestamo;
use App\Models\PrestamoDetalle;
use App\Models\PrestamoPago;
use App\Models\Recibo;
use App\Models\ReciboAporte;
use App\Models\ReciboPago;
use App\Models\ResumenAnual;
use App\Models\ResumenMensual;
use App\Models\Timbrado;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class PlanillaCobro extends Component
{
    use WithFileUploads;

    private const ESTADO_ACTIVO = 1;

    private const ESTADO_PAGO_PENDIENTE = 1;
    private const ESTADO_PAGO_PAGADO = 2;
    private const ESTADO_PAGO_PARCIAL = 3;
    private const ESTADO_PAGO_ANULADO = 4;
    private const ESTADO_PAGO_ENVIADO_PLANILLA = 5;
    private const ESTADO_PAGO_NO_PAGADO = 6;

    private const ESTADO_PRESTAMO_VIGENTE = 2;
    private const ESTADO_PRESTAMO_CANCELADO = 3;

    /*
    |--------------------------------------------------------------------------
    | TIPOS DE INGRESO DEL RESUMEN MENSUAL
    |--------------------------------------------------------------------------
    |
    | El capital toma preferentemente tipo_prestamos.tipo_ingreso_id.
    | Si todavía no está configurado, se utiliza el ID 9 como respaldo.
    |
    */
    private const TIPO_INGRESO_APORTE_PLANILLA = 4;
    private const TIPO_INGRESO_CAPITAL_PRESTAMO_DEFAULT = 9;
    private const TIPO_INGRESO_INTERES_PRESTAMO = 10;
    private const TIPO_INGRESO_IVA_PRESTAMO = 11;
    private const TIPO_INGRESO_PUNITORIO_PRESTAMO = 12;

    private array $tipoIngresoCapitalPorPrestamo = [];

    public $meses;
    public $mes;
    public $planilla;
    public $archivo;
    public $cantidad_excel = 0;
    public $monto_excel = 0;
    public $erroresDocumentos = [];
    public $verificado = false;
    public $formasCobro;
    public $bancos = [];
    public $cobros = [];
    public $total_abonado = 0;
    public $documento;
    public $persona = null;
    public $entidad = null;
    public $establecimiento = null;
    public $timbrado = null;
    public $procesando = false;

    public function mount(Planilla $planilla): void
    {
        $this->planilla = $planilla;

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

        $this->mes = $this->meses[(int) $this->planilla->mes];

        $this->formasCobro = FormaCobro::query()
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->orderBy('descripcion')
            ->get();

        $this->bancos = Banco::query()
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->where('id', '<>', 0)
            ->orderBy('descripcion')
            ->get();

        $this->reiniciarFormasCobro();

        $this->entidad = Entidad::find(1);
        $this->establecimiento = Establecimiento::find(1);
        $this->timbrado = Timbrado::find(1);
    }

    public function render()
    {
        return view('livewire.cobro.planilla-cobro');
    }

    public function verificar(): void
    {
        $this->validate([
            'archivo' => [
                'required',
                'file',
                'mimes:xlsx,xls',
            ],
        ], [
            'archivo.required' => 'Debe seleccionar un archivo Excel.',
            'archivo.mimes' => 'El archivo debe ser Excel (.xlsx o .xls).',
        ]);

        $this->resetErrorBag();
        $this->erroresDocumentos = [];
        $this->cantidad_excel = 0;
        $this->monto_excel = 0;
        $this->verificado = false;

        try {
            $planilla = Planilla::query()
                ->whereKey($this->planilla->id)
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->first();

            if (!$planilla) {
                throw new \Exception(
                    'La planilla no existe o fue anulada.'
                );
            }

            if ((int) $planilla->pagado === 1) {
                throw new \Exception(
                    'La planilla ya fue procesada anteriormente.'
                );
            }

            $datosExcel = $this->leerDatosExcel();

            if ($datosExcel->isEmpty()) {
                throw new \Exception(
                    'El archivo no tiene registros válidos.'
                );
            }

            $mapaPlanilla = $this->obtenerMapaPlanilla(false);

            foreach ($datosExcel as $item) {
                $detalle = $mapaPlanilla->get($item['documento']);

                if (!$detalle) {
                    $this->erroresDocumentos[] = [
                        'fila' => $item['fila'],
                        'documento' => $item['documento'],
                        'nombre' => $item['nombre'],
                        'monto' => $item['monto'],
                        'mensaje' => 'No existe en la planilla.',
                    ];

                    continue;
                }

                if ((int) $item['monto'] > (int) $detalle->saldo) {
                    $this->erroresDocumentos[] = [
                        'fila' => $item['fila'],
                        'documento' => $item['documento'],
                        'nombre' => $item['nombre'],
                        'monto' => $item['monto'],
                        'mensaje' => 'El monto supera el saldo de G. '
                            . number_format(
                                (int) $detalle->saldo,
                                0,
                                ',',
                                '.'
                            )
                            . '.',
                    ];
                }
            }

            if (!empty($this->erroresDocumentos)) {
                return;
            }

            $this->cantidad_excel = $datosExcel->count();
            $this->monto_excel = (int) $datosExcel->sum('monto');
            $this->verificado = true;

            $this->emit(
                'mensaje_exitoso',
                'El archivo fue verificado correctamente.'
            );
        } catch (\Throwable $e) {
            report($e);

            $this->verificado = false;
            $this->emit('mensaje_error', $e->getMessage());
        }
    }

    public function cancelar(): void
    {
        $this->reset([
            'archivo',
            'cantidad_excel',
            'monto_excel',
            'erroresDocumentos',
            'verificado',
            'total_abonado',
            'procesando',
        ]);

        $this->reiniciarFormasCobro();
        $this->resetErrorBag();
    }

    public function agregarCobro(): void
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

    public function quitarCobro(int $index): void
    {
        if (count($this->cobros) <= 1) {
            return;
        }

        unset($this->cobros[$index]);
        $this->cobros = array_values($this->cobros);
        $this->recalcularTotal();
    }

    public function cambioFormaCobro(
        $formaCobroId,
        int $index
    ): void {
        $forma = $this->formasCobro->firstWhere(
            'id',
            (int) $formaCobroId
        );

        $this->cobros[$index]['forma_cobro_id'] = $formaCobroId;
        $this->cobros[$index]['banco_ver'] = $forma
            ? (int) $forma->banco_ver
            : 0;

        if (!$forma || (int) $forma->banco_ver === 0) {
            $this->cobros[$index]['banco_id'] = '';
            $this->cobros[$index]['numero_comprobante'] = '';
        }
    }

    public function recalcularTotal(): void
    {
        $this->total_abonado = (int) collect($this->cobros)
            ->sum(function ($item) {
                return $this->limpiarMonto(
                    $item['monto'] ?? 0
                );
            });
    }

    public function grabar()
    {
        if ($this->procesando) {
            return false;
        }

        if (!$this->archivo) {
            $this->emit(
                'mensaje_error',
                'Debe seleccionar un archivo Excel.'
            );

            return false;
        }

        if (!$this->verificado) {
            $this->emit(
                'mensaje_error',
                'Primero debe verificar el archivo.'
            );

            return false;
        }

        if ((int) $this->monto_excel <= 0) {
            $this->emit(
                'mensaje_error',
                'El monto total del archivo debe ser mayor a cero.'
            );

            return false;
        }

        if (!$this->validarCobros()) {
            $this->emit(
                'mensaje_error',
                'Debe corregir las formas de cobro antes de grabar.'
            );

            return false;
        }

        if (!$this->persona || empty($this->persona['id'])) {
            $this->emit(
                'mensaje_error',
                'Debe seleccionar una persona válida.'
            );

            return false;
        }

        if (!$this->establecimiento || !$this->timbrado) {
            $this->emit(
                'mensaje_error',
                'No se encontró la configuración del establecimiento o timbrado.'
            );

            return false;
        }

        $this->procesando = true;
        $recibo = null;

        try {
            DB::transaction(function () use (&$recibo) {
                $usuarioId = (int) auth()->id();
                $ahora = now();

                $planilla = Planilla::query()
                    ->whereKey($this->planilla->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $planilla->estado_id !== self::ESTADO_ACTIVO) {
                    throw new \Exception(
                        'La planilla fue anulada y no puede cobrarse.'
                    );
                }

                if ((int) $planilla->pagado === 1) {
                    throw new \Exception(
                        'La planilla ya fue procesada anteriormente.'
                    );
                }

                $datosExcel = $this->leerDatosExcel();
                $mapaPlanilla = $this->obtenerMapaPlanilla(true);

                $this->validarDatosParaGrabar(
                    $datosExcel,
                    $mapaPlanilla
                );

                $numeracion = Numeracion::query()
                    ->where('timbrado_id', $this->timbrado->id)
                    ->where(
                        'establecimiento_id',
                        $this->establecimiento->id
                    )
                    ->where('modulo', 'RECIBO')
                    ->lockForUpdate()
                    ->first();

                if (!$numeracion) {
                    throw new \Exception(
                        'No se encontró la numeración del recibo.'
                    );
                }

                $numeroActual = (int) $numeracion->numero_siguiente;

                $tipo = strtoupper(
                    $planilla->tipoAsociado->descripcion
                );

                $descripcionTipo = (int) $planilla->tipo_asociado_id === 3
                    ? ($planilla->institucion->descripcion
                        ?? 'SIN INSTITUCIÓN')
                    : 'JUBILADOS';

                $concepto = 'COBRO PLANILLA '
                    . $tipo
                    . ' '
                    . $descripcionTipo
                    . ' '
                    . $planilla->planilla_numero
                    . '/'
                    . $planilla->planilla_anio;

                $recibo = Recibo::create([
                    'persona_id' => $this->persona['id'],
                    'tipo_recibo_id' => 4,
                    'sucursal' => $this->establecimiento->sucursal,
                    'general' => $this->establecimiento->general,
                    'numero' => $numeroActual,
                    'fecha' => $ahora,
                    'concepto' => $concepto,
                    'monto_total' => (int) $this->monto_excel,
                    'monto_abonado' => (int) $this->total_abonado,
                    'monto_devuelto' => 0,
                    'estado_id' => self::ESTADO_ACTIVO,
                    'anulado' => 0,
                    'user_id' => $usuarioId,
                ]);

                $numeracion->update([
                    'numero_siguiente' => $numeroActual + 1,
                ]);

                $fechaAporte = Carbon::createFromDate(
                    $planilla->anio,
                    $planilla->mes,
                    1
                )->endOfMonth()->toDateString();

                $insertReciboAportes = [];
                $insertAportes = [];
                $totalesDistribuidos =
                    $this->totalesDistribucionCero();

                foreach ($datosExcel as $item) {
                    $detalle = $mapaPlanilla->get(
                        $item['documento']
                    );

                    if (!$detalle) {
                        throw new \Exception(
                            'El documento '
                            . $item['documento']
                            . ' no pertenece a la planilla.'
                        );
                    }

                    $montoPersona = (int) $item['monto'];

                    /*
                    |======================================================================
                    | AQUÍ SE DISTRIBUYE EL MONTO POR ASOCIADO
                    |======================================================================
                    |
                    | Orden global:
                    | 1. Aporte.
                    | 2. Mora de todos los préstamos.
                    | 3. IVA de mora.
                    | 4. Interés.
                    | 5. IVA del interés.
                    | 6. Capital.
                    |
                    */
                    $distribucion = $this->distribuirPagoAsociado(
                        $detalle,
                        $montoPersona,
                        $recibo,
                        $ahora,
                        $usuarioId
                    );

                    if ((int) $distribucion['sobrante'] > 0) {
                        throw new \Exception(
                            'El monto del documento '
                            . $item['documento']
                            . ' supera las obligaciones disponibles por G. '
                            . number_format(
                                (int) $distribucion['sobrante'],
                                0,
                                ',',
                                '.'
                            )
                            . '.'
                        );
                    }

                    foreach ([
                        'aporte',
                        'mora',
                        'mora_iva',
                        'interes',
                        'iva',
                        'capital',
                    ] as $campo) {
                        $totalesDistribuidos[$campo] +=
                            (int) $distribucion[$campo];
                    }

                    foreach (
                        $distribucion['capital_por_tipo']
                        as $tipoIngresoId => $montoCapital
                    ) {
                        if (!isset(
                            $totalesDistribuidos['capital_por_tipo'][
                                $tipoIngresoId
                            ]
                        )) {
                            $totalesDistribuidos['capital_por_tipo'][
                                $tipoIngresoId
                            ] = 0;
                        }

                        $totalesDistribuidos['capital_por_tipo'][
                            $tipoIngresoId
                        ] += (int) $montoCapital;
                    }

                    $detalle->pagado =
                        (int) $detalle->pagado
                        + $montoPersona;

                    $detalle->saldo = max(
                        0,
                        (int) $detalle->saldo - $montoPersona
                    );

                    $detalle->usuario_modificacion = $usuarioId;
                    $detalle->save();

                    $pagoAporte = (int) $distribucion['aporte'];

                    if ($pagoAporte > 0) {
                        $insertReciboAportes[] = [
                            'recibo_id' => $recibo->id,
                            'asociado_id' => $detalle->asociado_id,
                            'planilla' => 0,
                            'planilla_numero' => $planilla->planilla_numero,
                            'planilla_anio' => $planilla->planilla_anio,
                            'planilla_id' => $planilla->id,
                            'institucion_id' => $detalle->institucion_id,
                            'fecha_aporte' => $fechaAporte,
                            'mes' => $planilla->mes,
                            'anio' => $planilla->anio,
                            'aporte' => $pagoAporte,
                            'estado_id' => self::ESTADO_ACTIVO,
                            'user_id' => $usuarioId,
                            'usuario_modificacion' => $usuarioId,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ];

                        $insertAportes[] = [
                            'asociado_id' => $detalle->asociado_id,
                            'tipo_asociado_id' => $planilla->tipo_asociado_id,
                            'institucion_id' => $detalle->institucion_id,
                            'mes' => $planilla->mes,
                            'anio' => $planilla->anio,
                            'fecha_aporte' => $fechaAporte,
                            'aporte' => $pagoAporte,
                            'fecha_ingreso' => $ahora->toDateString(),
                            'recibo_id' => $recibo->id,
                            'estado_id' => self::ESTADO_ACTIVO,
                            'user_id' => $usuarioId,
                            'usuario_modificacion' => $usuarioId,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ];
                    }
                }

                if (!empty($insertReciboAportes)) {
                    ReciboAporte::insert($insertReciboAportes);
                }

                if (!empty($insertAportes)) {
                    Aporte::insert($insertAportes);
                }

                /*
                |--------------------------------------------------------------------------
                | CERRAR CONCEPTOS QUE NO FUERON COBRADOS
                |--------------------------------------------------------------------------
                */
                $this->cerrarConceptosNoCobrados(
                    $planilla,
                    $recibo,
                    $ahora,
                    $usuarioId
                );

                $this->guardarFormasCobro(
                    $recibo,
                    $ahora
                );

                $planilla->update([
                    'pagado' => 1,
                    'fecha_pagado' => $ahora->toDateString(),
                    'monto_pagado' => (int) $this->monto_excel,
                    'usuario_modificacion' => $usuarioId,
                ]);

                $this->actualizarResumenes(
                    $recibo,
                    $totalesDistribuidos
                );
            });
        } catch (\Throwable $e) {
            report($e);

            $this->procesando = false;
            $this->emit('mensaje_error', $e->getMessage());

            return false;
        }

        return redirect()
            ->route('recibo.show', $recibo->id)
            ->with(
                'message',
                'El cobro de la planilla fue registrado correctamente.'
            );
    }

    /**
     * Distribuye un monto entre aporte y todas las cuotas del asociado.
     */
    private function distribuirPagoAsociado(
        PlanillaDetalle $detalle,
        int $monto,
        Recibo $recibo,
        Carbon $fechaPago,
        int $usuarioId
    ): array {
        $disponible = $monto;
        $resultado = $this->totalesDistribucionCero();

        /*
        |--------------------------------------------------------------------------
        | 1. APORTE
        |--------------------------------------------------------------------------
        */
        $aporte = PlanillaAporte::query()
            ->where('planilla_detalle_id', $detalle->id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->where(
                'estado_pago_id',
                self::ESTADO_PAGO_ENVIADO_PLANILLA
            )
            ->lockForUpdate()
            ->first();

        if ($aporte && $disponible > 0) {
            $pagoAporte = $this->aplicarMonto(
                $disponible,
                (int) $aporte->saldo
            );

            if ($pagoAporte > 0) {
                $aporte->monto_pagado =
                    (int) $aporte->monto_pagado
                    + $pagoAporte;

                $aporte->saldo = max(
                    0,
                    (int) $aporte->saldo - $pagoAporte
                );

                $aporte->estado_pago_id =
                    (int) $aporte->saldo === 0
                        ? self::ESTADO_PAGO_PAGADO
                        : self::ESTADO_PAGO_PARCIAL;

                $aporte->usuario_modificacion = $usuarioId;
                $aporte->save();

                $resultado['aporte'] = $pagoAporte;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2 AL 6. PRÉSTAMOS
        |--------------------------------------------------------------------------
        |
        | Las cuotas se ordenan por vencimiento. Para dos cuotas con la misma
        | fecha, se utiliza préstamo, número de cuota e ID como desempate.
        |
        */
        $cuotas = PlanillaPrestamo::query()
            ->where('planilla_detalle_id', $detalle->id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->where(
                'estado_pago_id',
                self::ESTADO_PAGO_ENVIADO_PLANILLA
            )
            ->orderBy('fecha_vencimiento')
            ->orderBy('prestamo_id')
            ->orderBy('numero_cuota')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $componentes = [
            [
                'monto_mora',
                'monto_mora_pagado',
                'mora',
            ],
            [
                'monto_mora_iva',
                'monto_mora_iva_pagado',
                'mora_iva',
            ],
            [
                'monto_interes',
                'monto_interes_pagado',
                'interes',
            ],
            [
                'monto_iva',
                'monto_iva_pagado',
                'iva',
            ],
            [
                'monto_capital',
                'monto_capital_pagado',
                'capital',
            ],
        ];

        $aplicaciones = [];

        foreach ($cuotas as $cuota) {
            $aplicaciones[$cuota->id] = [
                'mora' => 0,
                'mora_iva' => 0,
                'interes' => 0,
                'iva' => 0,
                'capital' => 0,
                'total' => 0,
            ];
        }

        /*
        | Se recorre primero el componente y después todas las cuotas.
        | Así no se paga capital mientras exista mora o interés pendiente
        | en cualquiera de los préstamos del asociado.
        */
        foreach ($componentes as [
            $campoMonto,
            $campoPagado,
            $campoResultado,
        ]) {
            foreach ($cuotas as $cuota) {
                if ($disponible <= 0) {
                    break 2;
                }

                $pendiente = max(
                    0,
                    (int) $cuota->{$campoMonto}
                    - (int) $cuota->{$campoPagado}
                );

                $aplicado = $this->aplicarMonto(
                    $disponible,
                    $pendiente
                );

                if ($aplicado <= 0) {
                    continue;
                }

                $cuota->{$campoPagado} =
                    (int) $cuota->{$campoPagado}
                    + $aplicado;

                $cuota->monto_pagado =
                    (int) $cuota->monto_pagado
                    + $aplicado;

                $cuota->saldo = max(
                    0,
                    (int) $cuota->saldo - $aplicado
                );

                $aplicaciones[$cuota->id][$campoResultado] += $aplicado;
                $aplicaciones[$cuota->id]['total'] += $aplicado;
                $resultado[$campoResultado] += $aplicado;

                if ($campoResultado === 'capital') {
                    $tipoIngresoCapital =
                        $this->obtenerTipoIngresoCapital(
                            (int) $cuota->prestamo_id
                        );

                    if (!isset(
                        $resultado['capital_por_tipo'][
                            $tipoIngresoCapital
                        ]
                    )) {
                        $resultado['capital_por_tipo'][
                            $tipoIngresoCapital
                        ] = 0;
                    }

                    $resultado['capital_por_tipo'][
                        $tipoIngresoCapital
                    ] += $aplicado;
                }
            }
        }

        foreach ($cuotas as $cuota) {
            $aplicacion = $aplicaciones[$cuota->id];

            if ((int) $aplicacion['total'] <= 0) {
                continue;
            }

            $cuota->estado_pago_id =
                (int) $cuota->saldo === 0
                    ? self::ESTADO_PAGO_PAGADO
                    : self::ESTADO_PAGO_PARCIAL;

            $cuota->fecha_pago = $fechaPago->toDateString();
            $cuota->usuario_modificacion = $usuarioId;
            $cuota->save();

            $this->actualizarCuotaOriginal(
                $cuota,
                $aplicacion,
                $fechaPago,
                $usuarioId
            );

            $this->actualizarPrestamoCabecera(
                $cuota,
                $aplicacion,
                $fechaPago,
                $usuarioId
            );

            $this->actualizarHistoricoPrestamo(
                $cuota,
                $aplicacion,
                $recibo,
                $fechaPago,
                $usuarioId
            );
        }

        $resultado['sobrante'] = $disponible;

        return $resultado;
    }

    private function actualizarCuotaOriginal(
        PlanillaPrestamo $planillaPrestamo,
        array $aplicacion,
        Carbon $fechaPago,
        int $usuarioId
    ): void {
        $cuota = PrestamoDetalle::query()
            ->whereKey($planillaPrestamo->prestamo_detalle_id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->lockForUpdate()
            ->first();

        if (!$cuota) {
            throw new \Exception(
                'No se encontró la cuota original del préstamo ID '
                . $planillaPrestamo->prestamo_id
                . '.'
            );
        }

        $cuota->monto_capital_pagado =
            (int) $cuota->monto_capital_pagado
            + (int) $aplicacion['capital'];

        $cuota->monto_interes_pagado =
            (int) $cuota->monto_interes_pagado
            + (int) $aplicacion['interes'];

        $cuota->monto_iva_pagado =
            (int) $cuota->monto_iva_pagado
            + (int) $aplicacion['iva'];

        $cuota->monto_pagado =
            (int) $cuota->monto_pagado
            + (int) $aplicacion['total'];

        $cuota->saldo_capital = max(
            0,
            (int) $cuota->saldo_capital
            - (int) $aplicacion['capital']
        );

        $cuota->saldo_interes = max(
            0,
            (int) $cuota->saldo_interes
            - (int) $aplicacion['interes']
        );

        $cuota->saldo_iva = max(
            0,
            (int) $cuota->saldo_iva
            - (int) $aplicacion['iva']
        );

        /*
         * En prestamo_detalles no existen saldo_mora y saldo_mora_iva.
         * Por eso monto_mora y monto_mora_iva representan el pendiente
         * actual y se reducen cuando son cobrados. El histórico queda en
         * prestamo_pagos y planilla_prestamos.
         */
        $cuota->monto_mora = max(
            0,
            (int) $cuota->monto_mora
            - (int) $aplicacion['mora']
        );

        $cuota->monto_mora_iva = max(
            0,
            (int) $cuota->monto_mora_iva
            - (int) $aplicacion['mora_iva']
        );

        $cuota->saldo_total = max(
            0,
            (int) $cuota->saldo_total
            - (int) $aplicacion['total']
        );

        $cuota->fecha_pago = $fechaPago->toDateString();
        $cuota->estado_pago_id =
            (int) $cuota->saldo_total === 0
                ? self::ESTADO_PAGO_PAGADO
                : self::ESTADO_PAGO_PARCIAL;

        $cuota->usuario_modificacion = $usuarioId;
        $cuota->save();
    }

    private function actualizarPrestamoCabecera(
        PlanillaPrestamo $planillaPrestamo,
        array $aplicacion,
        Carbon $fechaPago,
        int $usuarioId
    ): void {
        $prestamo = Prestamo::query()
            ->whereKey($planillaPrestamo->prestamo_id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->lockForUpdate()
            ->first();

        if (!$prestamo) {
            throw new \Exception(
                'No se encontró el préstamo ID '
                . $planillaPrestamo->prestamo_id
                . '.'
            );
        }

        $prestamo->monto_capital_pagado =
            (int) $prestamo->monto_capital_pagado
            + (int) $aplicacion['capital'];

        $prestamo->monto_interes_pagado =
            (int) $prestamo->monto_interes_pagado
            + (int) $aplicacion['interes'];

        $prestamo->monto_iva_pagado =
            (int) $prestamo->monto_iva_pagado
            + (int) $aplicacion['iva'];

        $prestamo->monto_pagado =
            (int) $prestamo->monto_pagado
            + (int) $aplicacion['total'];

        $prestamo->saldo_capital = max(
            0,
            (int) $prestamo->saldo_capital
            - (int) $aplicacion['capital']
        );

        $prestamo->saldo_interes = max(
            0,
            (int) $prestamo->saldo_interes
            - (int) $aplicacion['interes']
        );

        $prestamo->saldo_iva = max(
            0,
            (int) $prestamo->saldo_iva
            - (int) $aplicacion['iva']
        );

        $prestamo->saldo_total = max(
            0,
            (int) $prestamo->saldo_total
            - (int) $aplicacion['total']
        );

        if ((int) $prestamo->saldo_total === 0) {
            $prestamo->estado_prestamo_id =
                self::ESTADO_PRESTAMO_CANCELADO;

            $prestamo->fecha_cancelacion =
                $fechaPago->toDateString();
        } else {
            $prestamo->estado_prestamo_id =
                self::ESTADO_PRESTAMO_VIGENTE;
        }

        $prestamo->usuario_modificacion = $usuarioId;
        $prestamo->save();
    }

    private function actualizarHistoricoPrestamo(
        PlanillaPrestamo $planillaPrestamo,
        array $aplicacion,
        Recibo $recibo,
        Carbon $fechaPago,
        int $usuarioId
    ): void {
        $historico = PrestamoPago::query()
            ->where(
                'planilla_prestamo_id',
                $planillaPrestamo->id
            )
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->lockForUpdate()
            ->first();

        $estadoPago = (int) $planillaPrestamo->saldo === 0
            ? self::ESTADO_PAGO_PAGADO
            : self::ESTADO_PAGO_PARCIAL;

        $observacion = 'COBRO POR PLANILLA. RECIBO N.º '
            . $recibo->numero;

        if (!$historico) {
            PrestamoPago::create([
                'prestamo_id' => $planillaPrestamo->prestamo_id,
                'prestamo_detalle_id' => $planillaPrestamo->prestamo_detalle_id,
                'planilla_prestamo_id' => $planillaPrestamo->id,
                'canal_cobro' => 1,
                'fecha_generacion' => now()->toDateString(),
                'fecha_pago' => $fechaPago->toDateString(),
                'monto_capital' => (int) $planillaPrestamo->monto_capital,
                'monto_interes' => (int) $planillaPrestamo->monto_interes,
                'monto_iva' => (int) $planillaPrestamo->monto_iva,
                'monto_mora' => (int) $planillaPrestamo->monto_mora,
                'monto_mora_iva' => (int) $planillaPrestamo->monto_mora_iva,
                'monto_total' => (int) $planillaPrestamo->monto_total,
                'saldo_capital' => max(
                    0,
                    (int) $planillaPrestamo->monto_capital
                    - (int) $planillaPrestamo->monto_capital_pagado
                ),
                'saldo_interes' => max(
                    0,
                    (int) $planillaPrestamo->monto_interes
                    - (int) $planillaPrestamo->monto_interes_pagado
                ),
                'saldo_iva' => max(
                    0,
                    (int) $planillaPrestamo->monto_iva
                    - (int) $planillaPrestamo->monto_iva_pagado
                ),
                'saldo_total' => (int) $planillaPrestamo->saldo,
                'monto_capital_pagado' => (int) $aplicacion['capital'],
                'monto_interes_pagado' => (int) $aplicacion['interes'],
                'monto_iva_pagado' => (int) $aplicacion['iva'],
                'monto_mora_pagado' => (int) $aplicacion['mora'],
                'monto_mora_iva_pagado' => (int) $aplicacion['mora_iva'],
                'monto_pagado' => (int) $aplicacion['total'],
                'estado_pago_id' => $estadoPago,
                'observaciones' => $observacion,
                'estado_id' => self::ESTADO_ACTIVO,
                'usuario_id' => $usuarioId,
                'usuario_modificacion' => $usuarioId,
            ]);

            return;
        }

        $historico->monto_capital_pagado =
            (int) $historico->monto_capital_pagado
            + (int) $aplicacion['capital'];

        $historico->monto_interes_pagado =
            (int) $historico->monto_interes_pagado
            + (int) $aplicacion['interes'];

        $historico->monto_iva_pagado =
            (int) $historico->monto_iva_pagado
            + (int) $aplicacion['iva'];

        $historico->monto_mora_pagado =
            (int) $historico->monto_mora_pagado
            + (int) $aplicacion['mora'];

        $historico->monto_mora_iva_pagado =
            (int) $historico->monto_mora_iva_pagado
            + (int) $aplicacion['mora_iva'];

        $historico->monto_pagado =
            (int) $historico->monto_pagado
            + (int) $aplicacion['total'];

        $historico->saldo_capital = max(
            0,
            (int) $historico->saldo_capital
            - (int) $aplicacion['capital']
        );

        $historico->saldo_interes = max(
            0,
            (int) $historico->saldo_interes
            - (int) $aplicacion['interes']
        );

        $historico->saldo_iva = max(
            0,
            (int) $historico->saldo_iva
            - (int) $aplicacion['iva']
        );

        $historico->saldo_total = max(
            0,
            (int) $historico->saldo_total
            - (int) $aplicacion['total']
        );

        $historico->fecha_pago = $fechaPago->toDateString();
        $historico->estado_pago_id = $estadoPago;
        $historico->observaciones = mb_substr(
            $observacion,
            0,
            500
        );
        $historico->usuario_modificacion = $usuarioId;
        $historico->save();
    }

    /**
     * Al cerrar la planilla, los conceptos que continúan con estado 5
     * quedan como NO PAGADOS. Las cuotas originales se liberan para que
     * puedan entrar en una planilla posterior.
     */
    private function cerrarConceptosNoCobrados(
        Planilla $planilla,
        Recibo $recibo,
        Carbon $fecha,
        int $usuarioId
    ): void {
        $detalleIds = PlanillaDetalle::query()
            ->where('planilla_id', $planilla->id)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->pluck('id');

        PlanillaAporte::query()
            ->whereIn('planilla_detalle_id', $detalleIds)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->where(
                'estado_pago_id',
                self::ESTADO_PAGO_ENVIADO_PLANILLA
            )
            ->update([
                'estado_pago_id' => self::ESTADO_PAGO_NO_PAGADO,
                'usuario_modificacion' => $usuarioId,
                'updated_at' => $fecha,
            ]);

        $prestamosNoCobrados = PlanillaPrestamo::query()
            ->whereIn('planilla_detalle_id', $detalleIds)
            ->where('estado_id', self::ESTADO_ACTIVO)
            ->where(
                'estado_pago_id',
                self::ESTADO_PAGO_ENVIADO_PLANILLA
            )
            ->lockForUpdate()
            ->get();

        foreach ($prestamosNoCobrados as $planillaPrestamo) {
            $planillaPrestamo->update([
                'estado_pago_id' => self::ESTADO_PAGO_NO_PAGADO,
                'usuario_modificacion' => $usuarioId,
            ]);

            PrestamoPago::query()
                ->where(
                    'planilla_prestamo_id',
                    $planillaPrestamo->id
                )
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->where(
                    'estado_pago_id',
                    self::ESTADO_PAGO_ENVIADO_PLANILLA
                )
                ->update([
                    'estado_pago_id' => self::ESTADO_PAGO_NO_PAGADO,
                    'fecha_pago' => null,
                    'observaciones' => mb_substr(
                        'NO COBRADO EN PLANILLA. RECIBO N.º '
                        . $recibo->numero,
                        0,
                        500
                    ),
                    'usuario_modificacion' => $usuarioId,
                    'updated_at' => $fecha,
                ]);

            $cuotaOriginal = PrestamoDetalle::query()
                ->whereKey(
                    $planillaPrestamo->prestamo_detalle_id
                )
                ->where('estado_id', self::ESTADO_ACTIVO)
                ->lockForUpdate()
                ->first();

            if (
                $cuotaOriginal
                && (int) $cuotaOriginal->estado_pago_id
                    === self::ESTADO_PAGO_ENVIADO_PLANILLA
            ) {
                $nuevoEstado =
                    (int) $cuotaOriginal->monto_pagado > 0
                    && (int) $cuotaOriginal->saldo_total > 0
                        ? self::ESTADO_PAGO_PARCIAL
                        : self::ESTADO_PAGO_PENDIENTE;

                $cuotaOriginal->update([
                    'estado_pago_id' => $nuevoEstado,
                    'usuario_modificacion' => $usuarioId,
                ]);
            }
        }
    }

    private function guardarFormasCobro(
        Recibo $recibo,
        Carbon $ahora
    ): void {
        $insertCobros = [];

        foreach ($this->cobros as $cobro) {
            $formaCobroId = $cobro['forma_cobro_id'] ?? null;
            $bancoVer = (int) ($cobro['banco_ver'] ?? 0);
            $bancoId = $bancoVer === 1
                ? ($cobro['banco_id'] ?? 0)
                : 1;

            $monto = $this->limpiarMonto(
                $cobro['monto'] ?? 0
            );

            if (!$formaCobroId || $monto <= 0) {
                continue;
            }

            $insertCobros[] = [
                'recibo_id' => $recibo->id,
                'fecha' => $cobro['fecha_pago']
                    ?? $ahora->toDateString(),
                'forma_cobro_id' => $formaCobroId,
                'banco_id' => $bancoId ?: 0,
                'monto' => $monto,
                'numero_comprobante' =>
                    $cobro['numero_comprobante'] ?? '',
                'estado_id' => self::ESTADO_ACTIVO,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        if (!empty($insertCobros)) {
            ReciboPago::insert($insertCobros);
        }
    }

    private function actualizarResumenes(
        Recibo $recibo,
        array $totales
    ): void {
        $fechaResumen = Carbon::parse($recibo->fecha);
        $anioResumen = (int) $fechaResumen->year;
        $mesResumen = (int) $fechaResumen->month;

        /*
        |--------------------------------------------------------------------------
        | CLASIFICAR LOS INGRESOS DEL MES
        |--------------------------------------------------------------------------
        |
        | El IVA normal y el IVA de la mora se acumulan en un único tipo.
        | El capital se clasifica según tipo_prestamos.tipo_ingreso_id.
        |
        */
        $movimientos = [];
        $observaciones = [];

        $this->agregarMovimientoResumen(
            $movimientos,
            $observaciones,
            self::TIPO_INGRESO_APORTE_PLANILLA,
            (int) $totales['aporte'],
            'Ingreso por aporte cobrado mediante planilla'
        );

        $this->agregarMovimientoResumen(
            $movimientos,
            $observaciones,
            self::TIPO_INGRESO_INTERES_PRESTAMO,
            (int) $totales['interes'],
            'Ingreso por interés de préstamo cobrado mediante planilla'
        );

        $this->agregarMovimientoResumen(
            $movimientos,
            $observaciones,
            self::TIPO_INGRESO_IVA_PRESTAMO,
            (int) $totales['iva']
                + (int) $totales['mora_iva'],
            'Ingreso por IVA de préstamo cobrado mediante planilla'
        );

        $this->agregarMovimientoResumen(
            $movimientos,
            $observaciones,
            self::TIPO_INGRESO_PUNITORIO_PRESTAMO,
            (int) $totales['mora'],
            'Ingreso por interés punitorio cobrado mediante planilla'
        );

        foreach (
            $totales['capital_por_tipo']
            as $tipoIngresoId => $montoCapital
        ) {
            $this->agregarMovimientoResumen(
                $movimientos,
                $observaciones,
                (int) $tipoIngresoId,
                (int) $montoCapital,
                'Recuperación de capital de préstamo mediante planilla'
            );
        }

        $totalClasificado = (int) array_sum($movimientos);
        $totalRecibo = (int) $recibo->monto_total;

        if ($totalClasificado !== $totalRecibo) {
            throw new \Exception(
                'El total clasificado para los resúmenes no coincide '
                . 'con el total del recibo. Clasificado: G. '
                . number_format($totalClasificado, 0, ',', '.')
                . '. Recibo: G. '
                . number_format($totalRecibo, 0, ',', '.')
                . '.'
            );
        }

        foreach ($movimientos as $tipoIngresoId => $monto) {
            $this->sumarResumenMensual(
                $anioResumen,
                $mesResumen,
                (int) $tipoIngresoId,
                (int) $monto,
                $observaciones[$tipoIngresoId]
                    ?? 'Ingreso generado desde cobro de planilla'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RESUMEN ANUAL
        |--------------------------------------------------------------------------
        |
        | Se suma el total clasificado una sola vez para evitar duplicaciones.
        |
        */
        $resumenAnual = ResumenAnual::query()
            ->where('anio', $anioResumen)
            ->lockForUpdate()
            ->first();

        if (!$resumenAnual) {
            $saldoInicialAnual = 0;

            $anualAnterior = ResumenAnual::query()
                ->where('anio', $anioResumen - 1)
                ->first();

            if ($anualAnterior) {
                $saldoInicialAnual =
                    (int) $anualAnterior->saldo_final;
            }

            $resumenAnual = ResumenAnual::create([
                'anio' => $anioResumen,
                'saldo_inicial' => $saldoInicialAnual,
                'total_ingreso' => 0,
                'total_egreso' => 0,
                'saldo_final' => $saldoInicialAnual,
                'fecha_calculo' => null,
                'usuario_calculo' => null,
                'observacion' =>
                    'Creado automáticamente desde cobro de planilla',
            ]);
        }

        $resumenAnual->total_ingreso =
            (int) $resumenAnual->total_ingreso
            + $totalClasificado;

        $resumenAnual->saldo_final =
            (int) $resumenAnual->saldo_inicial
            + (int) $resumenAnual->total_ingreso
            - (int) $resumenAnual->total_egreso;

        $resumenAnual->save();
    }

    private function agregarMovimientoResumen(
        array &$movimientos,
        array &$observaciones,
        int $tipoIngresoId,
        int $monto,
        string $observacion
    ): void {
        if ($tipoIngresoId <= 0 || $monto <= 0) {
            return;
        }

        if (!isset($movimientos[$tipoIngresoId])) {
            $movimientos[$tipoIngresoId] = 0;
            $observaciones[$tipoIngresoId] = $observacion;
        }

        $movimientos[$tipoIngresoId] += $monto;
    }

    private function sumarResumenMensual(
        int $anio,
        int $mes,
        int $tipoIngresoId,
        int $monto,
        string $observacion
    ): void {
        if ($monto <= 0) {
            return;
        }

        $resumenMensual = ResumenMensual::query()
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('tipo_ingreso_id', $tipoIngresoId)
            ->whereNull('tipo_egreso_id')
            ->lockForUpdate()
            ->first();

        if (!$resumenMensual) {
            $resumenMensual = ResumenMensual::create([
                'anio' => $anio,
                'mes' => $mes,
                'tipo_ingreso_id' => $tipoIngresoId,
                'tipo_egreso_id' => null,
                'tipo_movimiento' => 'I',
                'total_ingreso' => 0,
                'total_egreso' => 0,
                'fecha_calculo' => null,
                'usuario_calculo' => null,
                'observacion' => $observacion,
            ]);
        }

        $resumenMensual->total_ingreso =
            (int) $resumenMensual->total_ingreso
            + $monto;

        $resumenMensual->save();
    }

    private function validarDatosParaGrabar(
        Collection $datosExcel,
        Collection $mapaPlanilla
    ): void {
        if ($datosExcel->isEmpty()) {
            throw new \Exception(
                'El archivo no contiene registros válidos.'
            );
        }

        foreach ($datosExcel as $item) {
            $detalle = $mapaPlanilla->get($item['documento']);

            if (!$detalle) {
                throw new \Exception(
                    'El documento '
                    . $item['documento']
                    . ' no pertenece a la planilla.'
                );
            }

            if ((int) $item['monto'] > (int) $detalle->saldo) {
                throw new \Exception(
                    'El monto del documento '
                    . $item['documento']
                    . ' supera el saldo disponible.'
                );
            }
        }

        $montoArchivo = (int) $datosExcel->sum('monto');

        if ($montoArchivo !== (int) $this->monto_excel) {
            throw new \Exception(
                'El contenido del archivo cambió después de la verificación.'
            );
        }
    }

    private function validarCobros(): bool
    {
        foreach ($this->cobros as $indice => $cobro) {
            $this->cobros[$indice]['monto'] =
                $this->limpiarMonto($cobro['monto'] ?? 0);
        }

        $this->recalcularTotal();

        $this->validate([
            'cobros' => [
                'required',
                'array',
                'min:1',
            ],
            'cobros.*.fecha_pago' => [
                'required',
                'date',
            ],
            'cobros.*.forma_cobro_id' => [
                'required',
                'exists:forma_cobros,id',
            ],
            'cobros.*.monto' => [
                'required',
                'numeric',
                'min:1',
            ],
        ], [
            'cobros.required' =>
                'Debe ingresar al menos una forma de cobro.',
            'cobros.*.fecha_pago.required' =>
                'Debe ingresar la fecha del cobro.',
            'cobros.*.forma_cobro_id.required' =>
                'Debe seleccionar una forma de cobro.',
            'cobros.*.monto.required' =>
                'Debe ingresar el monto.',
        ]);

        foreach ($this->cobros as $indice => $cobro) {
            if (
                !empty($cobro['banco_ver'])
                && empty($cobro['banco_id'])
            ) {
                $this->addError(
                    "cobros.$indice.banco_id",
                    'Debe seleccionar un banco.'
                );
            }

            if (
                !empty($cobro['banco_ver'])
                && empty($cobro['numero_comprobante'])
            ) {
                $this->addError(
                    "cobros.$indice.numero_comprobante",
                    'Debe ingresar el número de comprobante.'
                );
            }
        }

        if ((int) $this->total_abonado !== (int) $this->monto_excel) {
            $this->addError(
                'total_abonado',
                'El total abonado debe ser igual al monto del Excel.'
            );
        }

        return $this->getErrorBag()->isEmpty();
    }

    private function leerDatosExcel(): Collection
    {
        $hojas = Excel::toArray([], $this->archivo);

        if (empty($hojas) || empty($hojas[0])) {
            throw new \Exception(
                'El archivo no contiene datos.'
            );
        }

        $filas = collect($hojas[0])
            ->slice(1)
            ->values();

        $datos = $filas
            ->map(function ($fila, $indice) {
                return [
                    'fila' => $indice + 2,
                    'documento' => $this->limpiarDocumento(
                        $fila[0] ?? null
                    ),
                    'nombre' => trim(
                        (string) ($fila[1] ?? '')
                    ),
                    'monto' => $this->limpiarMonto(
                        $fila[2] ?? 0
                    ),
                ];
            })
            ->filter(function ($item) {
                return !empty($item['documento'])
                    && (int) $item['monto'] > 0;
            });

        /*
         * Si el mismo documento aparece en varias filas, sus montos se suman
         * para evitar que un detalle se actualice dos veces de forma separada.
         */
        return $datos
            ->groupBy('documento')
            ->map(function ($items, $documento) {
                return [
                    'fila' => $items->pluck('fila')->implode(', '),
                    'documento' => $documento,
                    'nombre' => $items->first()['nombre'] ?? '',
                    'monto' => (int) $items->sum('monto'),
                ];
            })
            ->values();
    }

    private function obtenerMapaPlanilla(
        bool $bloquear
    ): Collection {
        $consulta = PlanillaDetalle::query()
            ->join(
                'asociados',
                'asociados.id',
                '=',
                'planilla_detalles.asociado_id'
            )
            ->join(
                'personas',
                'personas.id',
                '=',
                'asociados.persona_id'
            )
            ->where(
                'planilla_detalles.planilla_id',
                $this->planilla->id
            )
            ->where(
                'planilla_detalles.estado_id',
                self::ESTADO_ACTIVO
            )
            ->select([
                'planilla_detalles.*',
                'personas.documento',
                'personas.nombre',
                'personas.apellido',
            ]);

        if ($bloquear) {
            $consulta->lockForUpdate();
        }

        return $consulta
            ->get()
            ->mapWithKeys(function ($detalle) {
                $documento = $this->limpiarDocumento(
                    $detalle->documento
                );

                return [$documento => $detalle];
            });
    }

    private function aplicarMonto(
        int &$disponible,
        int $pendiente
    ): int {
        if ($disponible <= 0 || $pendiente <= 0) {
            return 0;
        }

        $aplicado = min($disponible, $pendiente);
        $disponible -= $aplicado;

        return $aplicado;
    }

    /**
     * Obtiene el tipo de ingreso que corresponde a la recuperación de
     * capital según el tipo de préstamo. Para préstamos antiguos o todavía
     * no configurados utiliza el ID 9.
     */
    private function obtenerTipoIngresoCapital(
        int $prestamoId
    ): int {
        if (isset(
            $this->tipoIngresoCapitalPorPrestamo[$prestamoId]
        )) {
            return $this->tipoIngresoCapitalPorPrestamo[
                $prestamoId
            ];
        }

        $tipoIngresoId = DB::table('prestamos as pr')
            ->leftJoin(
                'tipo_prestamos as tp',
                'tp.id',
                '=',
                'pr.tipo_prestamo_id'
            )
            ->where('pr.id', $prestamoId)
            ->value('tp.tipo_ingreso_id');

        $tipoIngresoId = (int) $tipoIngresoId;

        if ($tipoIngresoId <= 0) {
            $tipoIngresoId =
                self::TIPO_INGRESO_CAPITAL_PRESTAMO_DEFAULT;
        }

        $this->tipoIngresoCapitalPorPrestamo[$prestamoId] =
            $tipoIngresoId;

        return $tipoIngresoId;
    }

    private function limpiarDocumento($documento): string
    {
        return preg_replace(
            '/[^0-9]/',
            '',
            trim((string) $documento)
        );
    }

    private function limpiarMonto($monto): int
    {
        $monto = trim((string) $monto);
        $monto = str_replace(
            ['.', ',', ' '],
            '',
            $monto
        );

        $monto = preg_replace('/[^0-9]/', '', $monto);

        return $monto === ''
            ? 0
            : (int) $monto;
    }

    private function reiniciarFormasCobro(): void
    {
        $this->cobros = [[
            'fecha_pago' => now()->toDateString(),
            'forma_cobro_id' => '',
            'banco_id' => '',
            'banco_ver' => 0,
            'monto' => 0,
            'numero_comprobante' => '',
        ]];
    }

    private function totalesDistribucionCero(): array
    {
        return [
            'aporte' => 0,
            'mora' => 0,
            'mora_iva' => 0,
            'interes' => 0,
            'iva' => 0,
            'capital' => 0,
            'capital_por_tipo' => [],
            'sobrante' => 0,
        ];
    }

    public function buscarPersona(): void
    {
        $this->persona = null;

        if (empty($this->documento)) {
            return;
        }

        $documentoLimpio = $this->limpiarDocumento(
            $this->documento
        );

        $persona = Persona::query()
            ->where('documento', $documentoLimpio)
            ->first();

        if (!$persona) {
            $this->emit(
                'mensaje_error',
                'No se encontró la persona.'
            );

            return;
        }

        $this->persona = [
            'id' => $persona->id,
            'ruc' => $persona->ruc,
            'nombre' => trim(
                $persona->nombre
                . ' '
                . $persona->apellido
            ),
        ];
    }
}
