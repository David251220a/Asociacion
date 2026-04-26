<?php

namespace App\Http\Livewire\Asociar;

use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\EstadoCivil;
use App\Models\Institucion;
use App\Models\Numeraciones;
use App\Models\Persona;
use App\Models\Sexo;
use App\Models\Solicitud;
use App\Models\SolicitudFamiliar;
use App\Models\SolicitudFichaMedica;
use App\Models\TipoAsociado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class AsociarIndex extends Component
{
    public $mensajeValidacion;
    public $documento;
    public $paso = 0;
    public $nombre, $apellido, $fecha_nacimiento, $sexo_id, $estado_civil_id, $email, $celular, $tipo_id;
    public $sexos, $estadosCivils, $tipos;
    public $departamento_id, $distrito_id, $ciudad_id, $barrio, $domicilio, $descripcion_vivienda, $tipo_vivienda, $institucion_id;
    public $departamentos, $distritos, $ciudades, $instituciones;
    public $enfermedades, $otra_enfermedad, $medicamentos, $seguro_medico, $observacion_medica;
    public $familiares = [], $tipoFamiliares;
    public $solicitud_final_numero, $solicitud_final_anio, $correo_enviado;

    public $documento_frente;
    public $documento_reverso;
    public $selfie;
    public $acepta_terminos = false;

    use WithFileUploads;

    public function mount()
    {
        $this->paso = 1;
        $this->sexos = Sexo::all();
        $this->sexo_id = $this->sexos->first()->id;
        $this->estadosCivils = EstadoCivil::all();
        $this->estado_civil_id = $this->estadosCivils->first()->id;
        $this->tipos = TipoAsociado::all();
        $this->tipo_id = $this->tipos->first()->id;
        $this->cargar_departamentos();
        $this->cargar_distritos();
        $this->cargar_ciudades();
        $this->cargar_instituciones();
        $this->familiares = [
            ['tipo' => '', 'nombre' => '', 'apellido' => '', 'ci' => '', 'telefono' => '']
        ];
        $this->correo_enviado = false;
    }

    public function render()
    {
        return view('livewire.asociar.asociar-index');
    }

    private function cargar_departamentos()
    {
        $this->departamentos = Departamento::all();
        $this->departamento_id = $this->departamentos->first()->id;
    }

    private function cargar_distritos()
    {
        $this->distritos = Distrito::where('departamento_id', $this->departamento_id)->get();
        $this->distrito_id = $this->distritos->first()->id;
    }

    private function cargar_ciudades()
    {
        $this->ciudades = Ciudad::where('distrito_id', $this->distrito_id)->get();
        $this->ciudad_id = $this->ciudades->first()->id;
    }

    private function cargar_instituciones()
    {
        $this->instituciones = Institucion::where('estado_id', 1)->get();
        $this->institucion_id = $this->instituciones->first()->id;
    }

    public function agregarFamiliar()
    {
        $this->familiares[] = ['tipo' => '', 'nombre' => '', 'apellido' => '', 'ci' => '', 'telefono' => ''];
    }

    public function eliminarFamiliar($index)
    {
        unset($this->familiares[$index]);
        $this->familiares = array_values($this->familiares);
    }

    public function updatedDepartamentoId()
    {
        $this->distrito_id = null;
        $this->ciudad_id = null;
        $this->cargar_distritos();
        $this->cargar_ciudades();
    }

    public function updatedDistritoId()
    {
        $this->ciudad_id = null;
        $this->cargar_ciudades();
    }


    public function verificarDocumento()
    {
        if(empty($this->documento)){
            $this->emit('mensaje_error', 'El documento no puede ser estar vacio.');
            $this->paso = 1;
            return;
        }

        $documento = strtoupper(str_replace(['.', '-', ' '], '', $this->documento));

        if (!preg_match('/^[0-9]+[A-Z]?$/', $documento)) {
            $this->emit('mensaje_error', 'Formato de documento inválido.');
            return;
        }

        $existe = Persona::where('documento', $documento)->exists();
        if($existe){
            $this->emit('mensaje_error', 'Usted ya es pertenece a esta asociacion.');
            $this->paso = 1;
            return;
        }

        $aux_solicitud = Solicitud::where('documento', $documento)
        ->where('aprobado', 0)
        ->exists();

        if($aux_solicitud){
            $this->emit('mensaje_error', 'Usted ya tiene una solicitud pendiente.');
            $this->paso = 1;
            return;
        }

        $this->paso = 2;
    }

    public function volverPasoUno()
    {
        $this->paso = 1;
    }

    public function pasarPasoTres()
    {

        [$ok, $mensaje] = $this->validacionPasoDos();

        if (!$ok) {
            $this->emit('mensaje_error', $mensaje);
            $this->paso = 2;
            return;
        }

        $this->paso = 3;

    }

    private function validacionPasoDos()
    {
        if (empty($this->nombre)) {
            return [false, 'El nombre no puede estar vacío.'];
        }

        if (empty($this->apellido)) {
            return [false, 'El apellido no puede estar vacío.'];
        }

        if (empty($this->fecha_nacimiento)) {
            return [false, 'La fecha de nacimiento no puede estar vacía.'];
        }
        try {
            $fecha = Carbon::createFromFormat('Y-m-d', $this->fecha_nacimiento);
        } catch (\Exception $e) {
            return [false, 'mensaje_error', 'Debe ingresar una fecha válida.'];
        }

        if ($fecha->isFuture()) {
            return [false, 'mensaje_error', 'La fecha no puede ser futura.'];
        }

        if (empty($this->sexo_id)) {
            return [false, 'Debe seleccionar el sexo.'];
        }

        if (empty($this->estado_civil_id)) {
            return [false, 'Debe seleccionar el estado civil.'];
        }

        if (empty($this->celular)) {
            return [false, 'El número de celular no puede estar vacío.'];
        }

        if (empty($this->email)) {
            return [false, 'El email no puede estar vacío.'];
        }

        $email = strtolower(trim($this->email));

        $existeEmail = Persona::where('email', $email)->exists();

        if ($existeEmail) {
            return [false, 'El email ya está registrado a otro socio.'];
        }

        $this->email = $email;

        return [true, ''];
    }

    public function pasarPasoCuatro()
    {
        if(empty($this->barrio)){
            $this->emit('mensaje_error', 'El campo barrio no puede quedar vacio.');
            $this->paso = 3;
            return;
        }

        if(empty($this->domicilio)){
            $this->emit('mensaje_error', 'El campo domicilio no puede quedar vacio.');
            $this->paso = 3;
            return;
        }

        if(empty($this->descripcion_vivienda)){
            $this->emit('mensaje_error', 'El campo descripcion vivienda no puede quedar vacio.');
            $this->paso = 3;
            return;
        }

        if($this->tipo_vivienda == 0){
            $this->emit('mensaje_error', 'Debe seleccionar un tipo de vivienda.');
            $this->paso = 3;
            return;
        }

        $this->paso = 4;
    }

    public function pasarPasoCinco()
    {
        $nombre = trim($this->familiar_nombre ?? '');
        $apellido = trim($this->familiar_apellido ?? '');
        $ci = trim($this->familiar_ci ?? '');

        if ($nombre === '' && $apellido === '' && $ci === '') {
            $this->paso = 5;
            return;
        }

        if ($nombre === '' || $apellido === '' || $ci === '') {
            $this->emit('mensaje_error', 'Si ingresa un familiar debe completar nombre, apellido y CI.');
            return;
        }

        $this->paso = 5;
    }

    public function pasarPasoSeis()
    {
        $this->paso = 6;
    }

    public function volverPasoCinco()
    {
        $this->reset([
            'documento_frente',
            'documento_reverso',
            'selfie',
        ]);

        $this->paso = 5;
    }

    public function finalizarSolicitud()
    {
        if (!$this->documento_frente) {
            $this->emit('mensaje_error', 'Debe adjuntar el documento frente.');
            return;
        }

        if (!$this->documento_reverso) {
            $this->emit('mensaje_error', 'Debe adjuntar el documento reverso.');
            return;
        }

        if (!$this->selfie) {
            $this->emit('mensaje_error', 'Debe adjuntar la selfie.');
            return;
        }

        if (!$this->acepta_terminos) {
            $this->emit('mensaje_error', 'Debe aceptar los términos de asociación.');
            return;
        }

        try {
            $this->validate([
                'documento_frente' => 'required|mimes:jpg,jpeg,pdf',
                'documento_reverso' => 'required|mimes:jpg,jpeg,pdf',
                'selfie' => 'required|mimes:jpg,jpeg,pdf',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $mensaje = collect($e->errors())->flatten()->first();
            $this->emit('mensaje_error', $mensaje);
            return;
        }

        DB::beginTransaction();

        try {

            $anio = now()->year;

            $numeracion = Numeraciones::where('tipo', 1)
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

            if (!$numeracion) {
                $numero = 1;

                $numeracion = Numeraciones::create([
                    'tipo' => 1,
                    'anio' => $anio,
                    'descripcion' => 'Solicitud de asociación',
                    'numero' => 2
                ]);

            } else {
                $numero = $numeracion->numero;
                $numeracion->numero = $numero + 1;
                $numeracion->save();
            }

            $documento = strtoupper(str_replace(['.', '-', ' '], '', $this->documento));
            $rutaFrente = $this->documento_frente->store('solicitudes/documentos', 'public');
            $rutaReverso = $this->documento_reverso->store('solicitudes/documentos', 'public');
            $rutaSelfie = $this->selfie->store('solicitudes/selfies', 'public');

            $solicitud = Solicitud::create([
                'departamento_id' => $this->departamento_id,
                'distrito_id' => $this->distrito_id,
                'ciudad_id' => $this->ciudad_id,
                'tipo_persona_id' => 1,
                'sexo_id' => $this->sexo_id,
                'estado_civil_id' => $this->estado_civil_id,
                'tipo_vivienda_id' => $this->tipo_vivienda,
                'institucion_id' => $this->institucion_id,
                'anio' => $anio,
                'numero_solicitud' => $numero,
                'fecha_solicitud' => now()->toDateString(),
                'aprobado' => 0,
                'fecha_aprobacion_o_rechazo' => null,
                'motivo_rechazo' => '',
                'numero_socio' => 0,
                'acta' => 0,
                'documento' => $documento,
                'nombre' => mb_strtoupper($this->nombre, 'UTF-8'),
                'apellido' => mb_strtoupper($this->apellido, 'UTF-8'),
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'direccion' => $this->domicilio,
                'barrio' => $this->barrio,
                'celular' => $this->celular,
                'email' => $this->email,
                'vivienda' => $this->descripcion_vivienda,
                'documento_frente' => $rutaFrente,
                'documento_reverso' => $rutaReverso,
                'selfi' => $rutaSelfie,
                'usuario_modificacion' => 0,
                'estado_id' => 1,
                'acepto_condiciones' => $this->acepta_terminos ? 1 : 0,
            ]);

            foreach ($this->familiares as $f) {
                $nombre = trim($f['nombre'] ?? '');
                $apellido = trim($f['apellido'] ?? '');
                $ci = trim($f['ci'] ?? '');

                if ($nombre !== '' || $apellido !== '' || $ci !== '') {

                    SolicitudFamiliar::create([
                        'solicitud_id' => $solicitud->id,
                        'tipo_familiar_id' => $f['tipo'] ?? null,
                        'ci' => $ci,
                        'nombre' => mb_strtoupper($nombre, 'UTF-8'),
                        'apellido' => mb_strtoupper($apellido, 'UTF-8'),
                        'celular' => $f['telefono'] ?? null,
                        'estado_id' => 1
                    ]);
                }
            }

            $enfermedades = $this->enfermedades ?? [];

            SolicitudFichaMedica::create([
                'solicitud_id' => $solicitud->id,
                'cancer' => in_array('CANCER', $enfermedades) ? 1 : 0,
                'diabetes' => in_array('DIABETES', $enfermedades) ? 1 : 0,
                'presion_alta' => in_array('PRESION ALTA', $enfermedades) ? 1 : 0,
                'otro' => $this->otra_enfermedad,
                'medicamentos' => $this->medicamentos,
                'seguro_particular' => $this->seguro_medico == 'PARTICULAR' ? 1 : 0,
                'seguro_ips' => $this->seguro_medico == 'IPS' ? 1 : 0,
                'seguro_ninguno' => $this->seguro_medico == 'NINGUNO' ? 1 : 0,
                'observacion' => $this->observacion_medica,
                'estado_id' => 1,
            ]);

            DB::commit();
            $this->solicitud_final_numero = str_pad($numero, 5, '0', STR_PAD_LEFT);
            $this->solicitud_final_anio = $anio;
            $this->paso = 7;
            $this->emit('mensaje_exitoso', 'Solicitud enviada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->emit('mensaje_error', $e->getMessage());
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ENVÍO DE CORREO - FUERA DE LA TRANSACCIÓN
        |--------------------------------------------------------------------------
        */
        try {
            // Acá más adelante enviás el PDF por correo
            // Mail::to($this->email)->send(new SolicitudAsociacionMail($solicitud));

            $this->correo_enviado = true;

        } catch (\Throwable $e) {
            $this->correo_enviado = false;

            // No corta el proceso, solo avisa
            // opcional: Log::error($e->getMessage());
        }

        $this->paso = 7;
        $this->emit('mensaje_exitoso', 'Solicitud enviada correctamente.');
    }
}
