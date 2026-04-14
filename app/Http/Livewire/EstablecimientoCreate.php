<?php

namespace App\Http\Livewire;

use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Distrito;
use Livewire\Component;

class EstablecimientoCreate extends Component
{
    public $departamento, $distrito, $ciudad, $departamento_id, $distrito_id, $ciudad_id;

    public function mount()
    {
       $this->departamento = Departamento::all();
        $this->departamento_id = 1;
        $this->cargarDistritos();
        $this->cargarCiudades();
    }

   public function updatedDepartamentoId($value)
    {
        $this->distrito = Distrito::where('departamento_id', $value)->get();

        $this->distrito_id = $this->distrito->count() > 0 ? $this->distrito->first()->id : null;

        $this->cargarCiudades();
    }

    public function updatedDistritoId($value)
    {
        $this->ciudad = Ciudad::where('distrito_id', $value)->get();

        $this->ciudad_id = $this->ciudad->count() > 0 ? $this->ciudad->first()->id : null;
    }

    private function cargarDistritos()
    {
        $this->distrito = Distrito::where('departamento_id', $this->departamento_id)->get();

        $this->distrito_id = $this->distrito->count() > 0 ? $this->distrito->first()->id : null;
    }

    private function cargarCiudades()
    {
        $this->ciudad = Ciudad::where('distrito_id', $this->distrito_id)->get();

        $this->ciudad_id = $this->ciudad->count() > 0 ? $this->ciudad->first()->id : null;
    }
    
    public function render()
    {
        return view('livewire.establecimiento-create');
    }

    
}
