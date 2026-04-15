<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\Entidad;
use App\Models\Factura;
use App\Models\FacturaAporte;
use App\Models\Planilla;
use App\Models\Sifen;
use App\Services\SifenServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public $sifen;

    public function __construct()
    {
        $this->middleware('permission:factura.index')->only('index');
        $this->middleware('permission:factura.show')->only('show');
        $this->middleware('permission:factura.anular')->only('anular');
        $this->middleware('permission:factura.aporte')->only('aporte');
    }

    public function index(Request $request)
    {
        $estado = $request->estado ?? 0;
        $fecha_desde = $request->fecha_desde 
        ? Carbon::parse($request->fecha_desde)->toDateString()
        : now()->toDateString();

        $fecha_hasta = $request->fecha_hasta 
        ? Carbon::parse($request->fecha_hasta)->toDateString()
        : now()->toDateString();

        $data = Factura::query()
        ->whereBetween('fecha_factura', [$fecha_desde, $fecha_hasta])
        ->when($estado != 0, fn($q) => $q->where('estado_id', $estado))
        ->orderByDesc('factura_sucursal')
        ->orderByDesc('factura_general')
        ->orderByDesc('factura_numero')
        ->paginate(50);

        return view('factura.index', compact('data', 'fecha_desde', 'fecha_hasta'));
    }

    public function show(Factura $factura)
    {
        $entidad = Entidad::find(1);
        $data = collect();

        if ($factura->tipo_factura_id == 1) {
            $detalle = FacturaAporte::where('factura_id', $factura->id)->first();

            if ($detalle) {
                if ((int) $detalle->planilla === 0) {
                    $planillaId = str_pad($detalle->planilla_numero, 5, '0', STR_PAD_LEFT) . '/' . $detalle->planilla_anio;
                    $descripcion = "APORTE {$factura->mes}/{$factura->anio} PLANILLA N° {$planillaId}";
                } else {
                    $descripcion = "APORTE MES " . strtoupper($this->nombreMes($detalle->mes)) . "/{$detalle->anio}";
                }

                $data = collect([
                    (object)[
                        'descripcion' => $descripcion,
                        'cantidad' => 1,
                        'precio' => $factura->monto_total,
                        'exento' => $factura->monto_total,
                        'grabado_5' => 0,
                        'grabado_10' => 0,
                        'iva_10' => 0,
                        'iva_5' => 0,
                        'total' => $factura->monto_total,
                    ]
                ]);
            }
        }

        return view('factura.show', compact('factura', 'entidad', 'data'));
    }

    public function anular(Factura $factura, SifenServices $sifen)
    {
       if (!$factura->sifen || $factura->sifen->sifen_estado !== 'APROBADO') {
            return redirect()->route('factura.index')->with('message', 'No puede anular la factura si no fue aprobada por SIFEN');
        }

        if ($factura->estado_id == 2) {
            return redirect()->route('factura.index')->with('message', 'La factura ya está anulada');
        }
        
        try {
            $data = $factura->sifen;
            $xml_formacion = $sifen->cancelacion($data, 'Facturacion incorrecta.');
            if (!$xml_formacion['success']) {
                throw new \Exception($xml_formacion['message']);
            }
            $xml = $xml_formacion['data']['xml_firmado'];
            $secuencia = $xml_formacion['data']['secuencia_evento'];
            $respuesta = $sifen->envioEvento($data, $xml, $secuencia, 2);
            if (!$respuesta['success']) {
                throw new \Exception($respuesta['message']);
            }

            if (strtoupper($respuesta['data']['status']) !== 'APROBADO') {
                throw new \Exception('SIFEN no aprobó la cancelación: ' . $respuesta['message']);
            }

            DB::transaction(function () use ($factura) {
                $detalle = FacturaAporte::where('factura_id', $factura->id)->first();

                if ($detalle && $detalle->planilla_id) {
                    $planilla = Planilla::find($detalle->planilla_id);

                    if ($planilla) {
                        $planilla->update([
                            'pagado' => 0,
                            'monto_pagado' => 0,
                            'fecha_pagado' => null,
                            'usuario_modificacion' => auth()->id(),
                        ]);
                    }
                }
                $fecha_anulado = now()->toDateString();
                $factura->update([
                    'estado_id' => 2,
                    'usuario_anulacion' => auth()->id(),
                    'fecha_anulado' => $fecha_anulado,
                    'motivo_anulacion' => 'Facturacion incorrecta',
                ]);

                FacturaAporte::where('factura_id', $factura->id)->update([
                    'estado_id' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'updated_at' => now(),
                ]);

                $factura->forma_pagos()->update([
                    'estado_id' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'updated_at' => now(),
                ]);

                Aporte::where('factura_id', $factura->id)->update([
                    'estado_id' => 2,
                    'usuario_modificacion' => auth()->id(),
                    'updated_at' => now(),
                ]);
            });


        } catch (\Throwable $e) {
            return redirect()->route('factura.index')->with('message',  $e->getMessage());
        }

        return redirect()->route('factura.index')->with('message', 'Factura anulada');
    }

    public function aporte()
    {
        return view('factura.aporte');
    }

    private function nombreMes($mes)
    {
        $meses = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];

        return $meses[(int) $mes] ?? '';
    }

    private function descripcionFacturaAporte(FacturaAporte $detalle, Factura $factura): string
    {
        if ((int) $detalle->planilla === 0) {
            $planillaId = str_pad($detalle->planilla_numero, 5, '0', STR_PAD_LEFT) . '/' . $detalle->planilla_anio;
            return "APORTE {$factura->mes}/{$factura->anio} PLANILLA N° {$planillaId}";
        }

        return "APORTE MES " . strtoupper($this->nombreMes($detalle->mes)) . "/{$detalle->anio}";
    }

}
