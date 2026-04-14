<?php

namespace App\Http\Livewire\Entidad;

use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Entidad;
use App\Models\Obligaciones;
use App\Models\Timbrado;
use App\Models\TipoTransaccion;
use Livewire\Component;

class EntidadIndex extends Component
{
    
    public $razon , $nombre, $ruc, $ruc_sin_digito, $digito, $email, $tipo_transaccion_id, $ambiente, $direccion,
    $numero_casa, $telefono, $districto_id, $ciudad_id, $codigo_set_id, $codigo_cliente_set,
    $tipo_contribuyente, $tipo_regimen, $departamento_id, $num_timbrado, $fecha_timbrado;
    public $distrito, $ciudad, $procesando;

    public function mount()
    {
        $entidad = Entidad::findOrFail(1);
        
        if ($entidad) {
            $this->razon = $entidad->razon_social;
            $this->nombre = $entidad->nombra_fantasia;
            $this->ruc = $entidad->ruc;
            $this->ruc_sin_digito = $entidad->ruc_sin_digito;
            $this->digito = $entidad->digito_verificador;
            $this->tipo_contribuyente = 1;
            $this->tipo_regimen = $entidad->tipo_regimen;
            $this->email = $entidad->email;
            $this->tipo_transaccion_id = $entidad->tipo_transaccion_id;
            $this->ambiente = $entidad->ambiente;
            $this->direccion = $entidad->direccion;
            $this->departamento_id = $entidad->departamento_id;
            $this->numero_casa = $entidad->numero_casa;
            $this->telefono = $entidad->telefono;
            $this->districto_id = $entidad->distrito_id;
            $this->ciudad_id = $entidad->ciudad_id;
            $this->codigo_cliente_set = $entidad->codigo_cliente_set;
            $this->codigo_set_id = $entidad->codigo_set_id;
        }else{
            $this->departamento_id = 1;
            $this->districto_id = 1;
            $this->ciudad_id = 1;
        }

        $timbrado = Timbrado::findOrFail(1);

        if ($timbrado){
            $this->num_timbrado = $timbrado->timbrado;
            $this->fecha_timbrado = $timbrado->fecha_inicio;
        }else{
            $this->num_timbrado = '';
            $this->fecha_timbrado = null;
        }
        
        $this->distrito = Distrito::where('departamento_id', $this->departamento_id)->get();
        $this->ciudad = Ciudad::where('distrito_id', $this->districto_id)->get();
        $this->procesando = false;
    }

    public function render()
    {
        $transaccion = TipoTransaccion::all();
        $depar = Departamento::all();
        $entidad = Entidad::all();
        $obligaciones = Obligaciones::where('estado_id', 1)->get();
        return view('livewire.entidad.entidad-index', compact('transaccion', 'depar', 'entidad', 'obligaciones'));
    }

    public function grabar()
    {
        $validar = $this->validar_grabacion();
        if($validar == false){
            return false;
        }

        $this->procesando = true;

        Entidad::UpdateOrCreate(
            ['id' => 1],
            [
                'razon_social' => $this->razon,
                'nombra_fantasia' => $this->nombre,
                'ruc' => $this->ruc,
                'ruc_sin_digito' => $this->ruc_sin_digito,
                'digito_verificador' => $this->digito,
                'tipo_contribuyente' => $this->tipo_contribuyente,
                'tipo_regimen' => $this->tipo_regimen,
                'email' => $this->email,
                'tipo_transaccion_id' => $this->tipo_transaccion_id,
                'ambiente' => $this->ambiente,
                'direccion' => $this->direccion,
                'departamento_id' => $this->departamento_id,
                'numero_casa' => $this->numero_casa,
                'telefono' => $this->telefono,
                'distrito_id' => $this->districto_id,
                'ciudad_id' => $this->ciudad_id,
                'codigo_set_id' => $this->codigo_set_id,
                'codigo_cliente_set' => $this->codigo_cliente_set,
            ]
        );

        Timbrado::UpdateOrCreate(
            ['id' => 1],
            [
                'timbrado' => $this->num_timbrado,
                'fecha_inicio' => $this->fecha_timbrado
            ]
        );

        $this->emit('mensaje_exitoso', 'Actualizado con exito.');
        $this->procesando = false;
    }

    public function validar_grabacion()
    {
         if(empty($this->razon)){
            $this->emit('mensaje_error', 'La razon social no puede ser nulo.');
            return false;
        }

        if(empty($this->nombre)){
            $this->emit('mensaje_error', 'El nombre de fantasia no puede ser nulo.');
            return false;
        }

        if(empty($this->num_timbrado)){
            $this->emit('mensaje_error', 'El numero de timbrado no puede ser nulo.');
            return false;
        }

        if (empty($this->fecha_timbrado)) {
            $this->emit('mensaje_error', 'La fecha del timbrado no puede ser nula.');
            return;
        }

        if (!strtotime($this->fecha_timbrado)) {
            $this->emit('mensaje_error', 'La fecha del timbrado no es válida.');
            return;
        }

        if(empty($this->email)){
            $this->emit('mensaje_error', 'El email no puede ser nulo.');
            return false;
        }

        if(empty($this->telefono)){
            $this->emit('mensaje_error', 'El numero de telefono no puede ser nulo.');
            return false;
        }

        if(empty($this->ruc)){
            $this->emit('mensaje_error', 'El ruc no puede ser nulo.');
            return false;
        }

        if(empty($this->ruc_sin_digito)){
            $this->emit('mensaje_error', 'El ruc sin digito no puede ser nulo.');
            return false;
        }

        if(empty($this->digito)){
            $this->emit('mensaje_error', 'El digito verificador no puede ser nulo.');
            return false;
        }

        if(empty($this->codigo_set_id)){
            $this->emit('mensaje_error', 'El codigo set id no puede ser nulo.');
            return false;
        }

        if(empty($this->codigo_cliente_set)){
            $this->emit('mensaje_error', 'El codigo cliente set no puede ser nulo.');
            return false;
        }

        return true;
    }

    
}
