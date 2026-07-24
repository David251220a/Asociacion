<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:usuario.index')->only('index');
        $this->middleware('permission:usuario.create')->only('create');
        $this->middleware('permission:usuario.store')->only('store');
        $this->middleware('permission:usuario.edit')->only('edit');
        $this->middleware('permission:usuario.update')->only('update');
    }

    public function index(Request $request)
    {
        $search = str_replace(',', '', $request->search);
        if (empty($search)){
            $data = User::orderBy('name', 'ASC')
            ->paginate(50);
        }else{
            $data = User::where('documento', $search)
            ->orWhere('name', 'LIKE', '%'. $search . '%')
            ->orWhere('lastname', 'LIKE', '%'. $search . '%')
            ->orderBy('name', 'ASC')
            ->paginate(50);
        }

        return view('usuario.index', compact('data', 'search'));
    }

    public function create()
    {
        $role = Role::get();
        return view('usuario.create', compact('role'));
    }

    public function edit(User $user)
    {
        $role = Role::get();
        return view('usuario.edit', compact('user', 'role'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'documento' => 'required|unique:users,documento',
            'name' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'celular' => 'required'
        ]
        , [
            'documento.unique' => 'Ya existe usuario con este documento',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Debe ingresar un correo válido',
            'email.unique' => 'Ya existe un usuario con este correo',
            'documento.required' => 'El documento es obligatorio',
        ]);

        $documento = str_replace('.', '', $request->documento);

        if (preg_match('/[a-zA-Z]+.*[0-9]+|[0-9]+.*[a-zA-Z]+/', $request->password)) {
            if(strlen($request->password) < 6){
                return redirect()->back()->withInput()
            ->withErrors('La contraseña debe contener al menos de 6 caracteres!.');
            }
        } else {
            return redirect()->back()->withInput()
            ->withErrors('La contraseña debe contener letras y numero. Ejemplo: Holamundo123!.');
        }

        $user = User::create([
            'username' => $request->username,
            'documento' => $documento,
            'name' =>  mb_strtoupper($request->name, 'UTF-8'),
            'lastname' =>  mb_strtoupper($request->lastname, 'UTF-8'),
            'email' => $request->email,
            'celular' => $request->celular,
            'password' => bcrypt($request->password),
        ]);

        $user->syncRoles($request->rol);
        return redirect()->route('user.index')->with('message', 'Se creo el usuario con exito ' . $user->name . '!.');
    }

    public function update(User $user, Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'documento' => [
                'required',
                Rule::unique('users', 'documento')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'name' => 'required',
            'lastname' => 'required',
            'celular' => 'required'
        ]
        , [
            'documento.unique' => 'Ya existe usuario con este documento',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'Debe ingresar un correo válido',
            'email.unique' => 'Ya existe un usuario con este correo',
            'documento.required' => 'El documento es obligatorio',
            'name.required' => 'El nombre es obligatorio',
            'lastname.required' => 'El apellido es obligatorio',
            'celular.required' => 'El celular es obligatorio',
        ]);

        $user->username = $request->username;
        $user->documento = str_replace('.', '', $request->documento);
        $user->lastname = $request->lastname;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->celular = $request->celular;

        if($request->password){
            if (preg_match('/[a-zA-Z]+.*[0-9]+|[0-9]+.*[a-zA-Z]+/', $request->password)) {
                if(strlen($request->password) < 6){
                    return redirect()->back()->withInput()
                ->withErrors('La contraseña debe contener al menos de 6 caracteres!.');
                }
            } else {
                return redirect()->back()->withInput()
                ->withErrors('La contraseña debe contener letras y numero. Ejemplo: Holamundo123!.');
            }

            $user->password = bcrypt($request->password);
        }

        $user->update();
        $user->syncRoles($request->rol);
        return redirect()->route('user.index')->with('message', 'Se edito con exito el usuario: ' . $user->name . '!.');
    }

    public function cambiar_contrase()
    {
        return view('usuario.cambiar_usuario');
    }

    public function cambiar_contrase_post(Request $request)
    {
        $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
        ], [
            'current_password.required' =>
                'Debe ingresar su contraseña actual.',

            'current_password.current_password' =>
                'La contraseña actual es incorrecta.',

            'password.required' =>
                'Debe ingresar la nueva contraseña.',

            'password.confirmed' =>
                'La confirmación de la contraseña no coincide.',

            'password.different' =>
                'La nueva contraseña debe ser diferente a la actual.',
        ]);

        $usuario = $request->user();
        $usuario->password = Hash::make($request->password);
        $usuario->save();
        $request->session()->regenerate();
        return redirect()->route('user.cambiar_contrase')->with('message', 'La contraseña fue actualizada correctamente.');
    }

    public function generarUsuariosAsociados()
    {
        $creados = 0;
        $omitidos = 0;

        Asociado::query()
        ->with('persona')
        ->where('estado_id', 1)
        ->whereHas('persona', function ($query) {
            $query->where('estado_id', 1);
        })
        ->chunkById(100, function ($asociados) use (
            &$creados,
            &$omitidos
        ) {
            foreach ($asociados as $asociado) {

                $persona = $asociado->persona;

                if (!$persona) {
                    $omitidos++;
                    continue;
                }

                $documento = trim((string) $persona->documento);

                if ($documento === '') {
                    $omitidos++;
                    continue;
                }

                /*
                * Si ya existe un usuario con el documento,
                * no se vuelve a crear.
                */
                $existe = User::where('documento', $documento)
                ->exists();

                if ($existe) {
                    $omitidos++;
                    continue;
                }

                /*
                * Preparar la parte inicial del correo.
                * Elimina puntos, espacios, guiones y caracteres especiales.
                */
                $correoBase = Str::lower(
                    Str::ascii($documento)
                );

                $correoBase = preg_replace(
                    '/[^a-z0-9]/',
                    '',
                    $correoBase
                );

                if ($correoBase === '') {
                    $omitidos++;
                    continue;
                }

                $correo = $correoBase . '@ajupem';
                $contador = 1;

                /*
                * Garantizar que el correo no se repita.
                */
                while (User::where('email', $correo)->exists()) {
                    $correo = $correoBase
                        . $contador
                        . '@ajupem';

                    $contador++;
                }

                DB::transaction(function () use (
                    $persona,
                    $documento,
                    $correo
                ) {
                    $usuario = new User();

                    $usuario->username = $documento;
                    $usuario->documento = $documento;
                    $usuario->name = strtoupper(trim($persona->nombre));
                    $usuario->lastname = strtoupper(trim($persona->apellido));
                    $usuario->email = $correo;

                    $usuario->password = Hash::make(
                        'Ajupem2026*'
                    );
                    $usuario->save();

                    /*
                    * Si utilizás Spatie Permission:
                    */
                    // $usuario->assignRole('ASOCIADO');
                });

                $creados++;
            }
        });

        return 'Usuarios Creados';
    }

}
