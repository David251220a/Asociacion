<?php

namespace App\Http\Livewire\Planilla;

use App\Services\PlanillaAporteService;
use Livewire\Component;

class PlanillaCreate extends Component
{
    public $tipo_asociado_id, $meses, $mes, $anio, $ver_boton, $procesando;
    public $data = [];
    public $cantidad = 0;
    public $total = 0;

    public function mount()
    {
        $this->tipo_asociado_id = 1;
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

        $this->mes = now()->month;
        $this->anio = now()->year;
        $this->ver_boton = 'none';
        $this->procesando = false;
    }

    public function render()
    {
        return view('livewire.planilla.planilla-create');
    }

    public function generar(PlanillaAporteService $service)
    {

        try {
            $this->ver_boton = 'none';
            $this->data = $service->generarDetalle(
                (int) $this->mes,
                (int) $this->anio,
                (int) $this->tipo_asociado_id
            );

            $this->cantidad = count($this->data);
            $this->total = collect($this->data)->sum('saldo'); // o 'monto_esperado'
            $this->ver_boton = 'block';
            $this->emit('mensaje_exitoso', 'Planilla generada correctamente.');


        } catch (\Throwable $th) {
            $this->ver_boton = 'none';
            $this->emit('mensaje_error', $th->getMessage());
        }
    }

    public function save(PlanillaAporteService $service)
    {
        try {
            $this->procesando = true;

            $this->ver_boton = 'none';
            $this->data = $service->guardarPlanilla(
                (int) $this->mes,
                (int) $this->anio,
                (int) $this->tipo_asociado_id
            );

            return redirect()->route('planilla.index')->with('message', 'Planilla generado con exito');

        } catch (\Throwable $th) {
            $this->ver_boton = 'none';
            $this->emit('mensaje_error', $th->getMessage());
        }
    }
}
