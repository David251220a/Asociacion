<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

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

}
