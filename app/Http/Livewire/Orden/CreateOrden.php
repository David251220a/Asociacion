<?php

namespace App\Http\Livewire\Orden;

use App\Models\Numeraciones;
use App\Models\OrdenPago;
use App\Models\OrdenPagoDetalle;
use App\Models\Persona;
use App\Models\TipoEgreso;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateOrden extends Component
{
    public $documento;
    public $total;
    public $persona;
    public $tipo_egresos, $tipo_egreso_id;
    public $concepto;
    public $observacion;

    public function mount()
    {
        $this->tipo_egresos = TipoEgreso::where('automatico', 0)->get();
        $this->tipo_egreso_id = $this->tipo_egresos->first()->id;
    }

    public function render()
    {
        return view('livewire.orden.create-orden');
    }

    public function buscarPersona()
    {
        $persona = Persona::where('documento', $this->documento)->first();
        if(empty($persona)){
            $this->emit('mensaje_error', 'No existe persona con este numero de documento');
            $this->persona = null;
            $this->documento = null;
            return false;
        }

        $this->persona = $persona;

    }

    public function guardarPendiente($id)
    {
        [$ok, $mensaje] = $this->validar_datos();

        if (!$ok) {
            $this->emit('mensaje_error', $mensaje);
            return;
        }

        $orden = null;

        DB::beginTransaction();

        try {

            $anio = now()->year;

            $numeracion = Numeraciones::where('tipo', 3)
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

            if (!$numeracion) {
                $numero = 1;

                Numeraciones::create([
                    'tipo' => 3,
                    'anio' => $anio,
                    'descripcion' => 'Orden de Pago',
                    'numero' => 2
                ]);
            } else {
                $numero = $numeracion->numero;
                $numeracion->numero = $numero + 1;
                $numeracion->save();
            }

            $total = str_replace('.','',$this->total);

            $orden = OrdenPago::create([
                'anio' => $anio,
                'numero' => $numero,
                'fecha' => now(),
                'tipo_egreso_id' => $this->tipo_egreso_id,
                'origen_id' => 0,
                'persona_id' => $this->persona->id,
                'beneficiario' => $this->persona->nombre . ' ' . $this->persona->apellido,
                'concepto' => $this->concepto,
                'observacion' => $this->observacion,
                'total' => $total,
                'estado_id' => 1,
                'estado_pago' => 0,
                'motivo_anulado' => '',
                'fecha_anulado' => null,
                'fecha_pago' => null,
                'user_id' => auth()->id(),
                'usuario_modificacion' => auth()->id(),
            ]);

            $tipo = TipoEgreso::find($this->tipo_egreso_id);

            OrdenPagoDetalle::create([
                'orden_pago_id' => $orden->id,
                'descripcion' => $tipo->descripcion,
                'cantidad' => 1,
                'precio' => $total,
                'subtotal' => $total,
                'estado_id' => 1,
                'user_id' => auth()->id(),
                'usuario_modificacion' => auth()->id(),
            ]);

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->emit('mensaje_error', $e->getMessage());
            return;
        }

        if($id == 0){
            return redirect()->route('orden.index')->with('message', 'Orden de Pago registrado correctamente.');
        }

        if($id == 1){
            return redirect()->route('orden.pago', $orden)->with('message', 'Orden de Pago registrado correctamente.');
        }

    }

    private  function validar_datos()
    {
        if(empty($this->persona)){
            return [false, 'Debe especificar el beneficiario'];
        }

        if(!$this->concepto){
            return [false, 'El concepto no puede estar nulo'];
        }

        $total = str_replace('.','',$this->total);
        if(empty($total)){
            return [false, 'Debe especificar el monto de la orden de pago'];
        }

        if ($total <= 0){
            return [false, 'El total no puede menor o igual a cero'];
        }
        return [true, ''];
    }

}
