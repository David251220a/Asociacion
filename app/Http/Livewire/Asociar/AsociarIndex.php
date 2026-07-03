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
use Dotenv\Validator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Mail\SolicitudRealizadaMail;
use Illuminate\Support\Facades\Mail;

class AsociarIndex extends Component
{
    use WithFileUploads;

    public $mensajeValidacion;
    public $documento;
    public $paso = 1;

    public $nombre = '';
    public $apellido = '';
    public $fecha_nacimiento;
    public $sexo_id;
    public $estado_civil_id;
    public $email = '';
    public $celular = '';
    public $tipo_id;

    public $sexos;
    public $estadosCivils;
    public $tipos;

    public $departamento_id;
    public $distrito_id;
    public $ciudad_id;
    public $barrio = '';
    public $domicilio = '';
    public $descripcion_vivienda = '';
    public $tipo_vivienda = 0;
    public $institucion_id;

    public $departamentos;
    public $distritos;
    public $ciudades;
    public $instituciones;

    public $enfermedades = [];
    public $otra_enfermedad = '';
    public $medicamentos = '';
    public $seguro_medico = 'NINGUNO';
    public $observacion_medica = '';

    public $familiares = [];

    public $solicitud_final_numero;
    public $solicitud_final_anio;
    public $correo_enviado = false;

    public $documento_frente;
    public $documento_reverso;
    public $selfie;
    public $acepta_terminos = false;

    public function mount()
    {
        $this->paso = 1;

        $this->sexos = Sexo::all();
        $this->sexo_id = optional($this->sexos->first())->id;

        $this->estadosCivils = EstadoCivil::all();
        $this->estado_civil_id = optional($this->estadosCivils->first())->id;

        $this->tipos = TipoAsociado::all();
        $this->tipo_id = optional($this->tipos->first())->id;

        $this->cargar_departamentos();
        $this->cargar_distritos();
        $this->cargar_ciudades();
        $this->cargar_instituciones();

        $this->familiares = [
            ['tipo' => '1', 'nombre' => '', 'apellido' => '', 'ci' => '', 'telefono' => '']
        ];
        try {
    $solicitud = Solicitud::find(1);

    $correoDestino = trim($solicitud->email ?? '');

    if ($correoDestino == '') {
        $correoDestino = 'davidortiz25122010@gmail.com';
    }

    Mail::to($correoDestino)
        ->send(new SolicitudRealizadaMail($solicitud));

    dd('envio');

} catch (\Throwable $e) {
    dd($e->getMessage(), $e->getFile(), $e->getLine());
}
    }

    public function render()
    {
        return view('livewire.asociar.asociar-index');
    }

    private function cargar_departamentos()
    {
        $this->departamentos = Departamento::all();

        if (empty($this->departamento_id)) {
            $this->departamento_id = optional($this->departamentos->first())->id;
        }
    }

    private function cargar_distritos()
    {

        $this->distritos = Distrito::where('departamento_id', $this->departamento_id)->get();

        if (empty($this->distrito_id) || !$this->distritos->contains('id', $this->distrito_id)) {
            $this->distrito_id = optional($this->distritos->first())->id;
        }
    }

    private function cargar_ciudades()
    {
        $this->ciudades = Ciudad::where('distrito_id', $this->distrito_id)->get();

        if (empty($this->ciudad_id) || !$this->ciudades->contains('id', $this->ciudad_id)) {
            $this->ciudad_id = optional($this->ciudades->first())->id;
        }
    }

    private function cargar_instituciones()
    {
        $this->instituciones = Institucion::where('estado_id', 1)->get();

        if (empty($this->institucion_id)) {
            $this->institucion_id = optional($this->instituciones->first())->id;
        }
    }

    public function cambiarDepartamento()
    {
        $this->distrito_id = null;
        $this->ciudad_id = null;

        $this->cargar_distritos();
        $this->cargar_ciudades();
    }

    public function cambiarDistrito()
    {
        $this->ciudad_id = null;

        $this->cargar_ciudades();
    }

    public function volverPasoUno()
    {
        $this->paso = 1;
    }

    public function volverPasoDos()
    {
        $this->paso = 2;
    }

    public function volverPasoTres()
    {
        $this->paso = 3;
    }

    public function volverPasoCuatro()
    {
        $this->paso = 4;
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

    public function verificarDocumento()
    {
        if (empty($this->documento)) {
            $this->emit('mensaje_error', 'El documento no puede estar vacío.');
            $this->paso = 1;
            return;
        }

        $documento = strtoupper(str_replace(['.', '-', ' '], '', $this->documento));

        if (!preg_match('/^[0-9]+[A-Z]?$/', $documento)) {
            $this->emit('mensaje_error', 'Formato de documento inválido.');
            return;
        }

        $existe = Persona::where('documento', $documento)->exists();

        if ($existe) {
            $this->emit('mensaje_error', 'Usted ya pertenece a esta asociación.');
            $this->paso = 1;
            return;
        }

        $aux_solicitud = Solicitud::where('documento', $documento)
            ->where('aprobado', 0)
            ->exists();

        if ($aux_solicitud) {
            $this->emit('mensaje_error', 'Usted ya tiene una solicitud pendiente.');
            $this->paso = 1;
            return;
        }

        $this->documento = $documento;
        $this->paso = 2;
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

        $fecha = Carbon::createFromFormat('d/m/Y', trim($this->fecha_nacimiento));

        // Verifica que la fecha exista realmente
        if ($fecha->format('d/m/Y') !== trim($this->fecha_nacimiento)) {
            return [false, 'Debe ingresar una fecha válida.'];
        }

        if ($fecha->isFuture()) {
            return [false, 'La fecha no puede ser futura.'];
        }

        if (empty($this->sexo_id)) {
            return [false, 'Debe seleccionar el sexo.'];
        }

        if (empty($this->estado_civil_id)) {
            return [false, 'Debe seleccionar el estado civil.'];
        }

        if (empty($this->tipo_id)) {
            return [false, 'Debe seleccionar el tipo de asociado.'];
        }

        if (empty($this->celular)) {
            return [false, 'El número de celular no puede estar vacío.'];
        }

        // if (empty($this->email)) {
        //     return [false, 'El email no puede estar vacío.'];
        // }
        // $email = strtolower(trim($this->email));
        // $existeEmail = Persona::where('email', $email)->exists();
        // if ($existeEmail) {
        //     return [false, 'El email ya está registrado a otro socio.'];
        // }
        // $this->email = $email;

        return [true, ''];
    }

    public function pasarPasoCuatro()
    {
        if (empty($this->barrio)) {
            $this->emit('mensaje_error', 'El campo barrio no puede quedar vacío.');
            $this->paso = 3;
            return;
        }

        if (empty($this->domicilio)) {
            $this->emit('mensaje_error', 'El campo domicilio no puede quedar vacío.');
            $this->paso = 3;
            return;
        }

        // if (empty($this->descripcion_vivienda)) {
        //     $this->emit('mensaje_error', 'El campo descripción vivienda no puede quedar vacío.');
        //     $this->paso = 3;
        //     return;
        // }

        if (empty($this->tipo_vivienda) || $this->tipo_vivienda == 0) {
            $this->emit('mensaje_error', 'Debe seleccionar un tipo de vivienda.');
            $this->paso = 3;
            return;
        }

        $this->paso = 4;
    }

    public function agregarFamiliar()
    {
        $this->familiares[] = [
            'tipo' => '1',
            'nombre' => '',
            'apellido' => '',
            'ci' => '',
            'telefono' => ''
        ];
    }

    public function eliminarFamiliar($index)
    {
        unset($this->familiares[$index]);
        $this->familiares = array_values($this->familiares);
    }

    public function pasarPasoCinco()
    {
        foreach ($this->familiares as $f) {
            $nombre = trim($f['nombre'] ?? '');
            $apellido = trim($f['apellido'] ?? '');
            $ci = trim($f['ci'] ?? '');

            if ($nombre === '' && $apellido === '' && $ci === '') {
                continue;
            }

            if ($nombre === '' || $apellido === '' || $ci === '') {
                $this->emit('mensaje_error', 'Si ingresa un familiar debe completar nombre, apellido y CI.');
                return;
            }
        }

        $this->paso = 5;
    }


    public function pasarPasoSeis()
    {
        $this->paso = 6;
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
                'selfie' => 'required|mimes:jpg,jpeg',
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

                Numeraciones::create([
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

            $fechaNacimiento = Carbon::createFromFormat('d/m/Y', $this->fecha_nacimiento)
            ->format('Y-m-d');

            $solicitud = Solicitud::create([
                'departamento_id' => $this->departamento_id,
                'distrito_id' => $this->distrito_id,
                'ciudad_id' => $this->ciudad_id,
                'tipo_persona_id' => 1,
                'sexo_id' => $this->sexo_id,
                'estado_civil_id' => $this->estado_civil_id,
                'tipo_vivienda_id' => $this->tipo_vivienda,
                'institucion_id' => $this->institucion_id,
                'tipo_asociado_id' => $this->tipo_id,
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
                'fecha_nacimiento' => $fechaNacimiento,
                'direccion' => $this->domicilio,
                'barrio' => $this->barrio,
                'celular' => $this->celular,
                'email' => $this->email ? strtolower(trim($this->email)) : null,
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
                        'tipo_familiar' => $f['tipo'] ?? null,
                        'documento' => $ci,
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

            $correoDestino = trim($solicitud->email ?? '');
            if ($correoDestino == '') {
                $correoDestino = 'davidortiz25122010@gmail.com';
            }
            Mail::to($correoDestino)
            ->send(new SolicitudRealizadaMail($solicitud));

            $this->solicitud_final_numero = str_pad($numero, 7, '0', STR_PAD_LEFT);
            $this->solicitud_final_anio = $anio;
            $this->correo_enviado = true;
            $this->paso = 7;

            $this->emit('mensaje_exitoso', 'Solicitud enviada correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->emit('mensaje_error', $e->getMessage());
            return;
        }
    }

    private function validarFechaNacimiento()
    {
        $validator = Validator::make([
            'fecha_nacimiento' => $this->fecha_nacimiento,
        ], [
            'fecha_nacimiento' => [
                'required',
                'regex:/^\d{2}\/\d{2}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    try {
                        $fecha = Carbon::createFromFormat('d/m/Y', $value);

                        if ($fecha->format('d/m/Y') !== $value) {
                            $fail('La fecha de nacimiento no es válida.');
                        }

                        if ($fecha->isFuture()) {
                            $fail('La fecha de nacimiento no puede ser futura.');
                        }

                    } catch (\Exception $e) {
                        $fail('La fecha de nacimiento no es válida.');
                    }
                },
            ],
        ], [
            'fecha_nacimiento.required' => 'Debe ingresar la fecha de nacimiento.',
            'fecha_nacimiento.regex' => 'La fecha debe tener el formato dd/mm/aaaa.',
        ]);

        if ($validator->fails()) {
            $this->emit('mensaje_error', $validator->errors()->first());
            return false;
        }

        return true;
    }

}
