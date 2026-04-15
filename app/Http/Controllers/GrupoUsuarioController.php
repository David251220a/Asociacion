<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GrupoUsuarioController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:rol.index')->only('index');
        $this->middleware('permission:rol.create')->only('create');
        $this->middleware('permission:rol.store')->only('store');
        $this->middleware('permission:rol.edit')->only('edit');
        $this->middleware('permission:rol.update')->only('update');
    }

    public function index()
    {
        $data = Role::paginate(50);
        return view('roles.index', compact('data'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ], [
            'name.unique' => 'Ya existe rol con este nombre',
            'name.required' => 'El nombre es obligatorio',
        ]);

        $role = Role::create($request->all());
        return redirect()->route('role.edit', $role)->with(['message' => 'Registro exitoso!']);
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('descripcion', 'ASC')->get();
        return view('roles.edit', [
            'data' => $role,
            'permissions' => $permissions
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $role->syncPermissions($request->permissions);
        $role->name = $request->name;
        $role->save();
        return redirect()->route('role.index')->with(['message' => 'Registro exitoso!']);
    }
}
