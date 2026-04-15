<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Role::create(['name' => 'admin']);
        // $admin = Role::find(1);

        $permission = Permission::create(['name' => 'rol.index', 'descripcion' => 'Rol Index'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'rol.create', 'descripcion' => 'Rol Crear'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'rol.edit', 'descripcion' => 'Rol Editar'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'rol.show', 'descripcion' => 'Rol Ver'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'rol.update', 'descripcion' => 'Rol Actualizar'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'rol.store', 'descripcion' => 'Rol Guardar'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'rol.delete', 'descripcion' => 'Rol Elimnar'])->syncRoles($admin);

        $permission = Permission::create(['name' => 'usuario.index', 'descripcion' => 'Usuario Index'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'usuario.create', 'descripcion' => 'Usuario Crear'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'usuario.edit', 'descripcion' => 'Usuario Editar'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'usuario.show', 'descripcion' => 'Usuario Ver'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'usuario.update', 'descripcion' => 'Usuario Actualizar'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'usuario.store', 'descripcion' => 'Usuario Guardar'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'usuario.delete', 'descripcion' => 'Usuario Elimnar'])->syncRoles($admin);

        $permission = Permission::create(['name' => 'asociado.index', 'descripcion' => 'Asociado: Index'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'asociado.create', 'descripcion' => 'Asociado: Crear'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'asociado.edit', 'descripcion' => 'Asociado: Editar'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'asociado.show', 'descripcion' => 'Asociado: Ver'])->syncRoles($admin);

        $permission = Permission::create(['name' => 'ficha_medica.create', 'descripcion' => 'Ficha Medica: Crear'])->syncRoles($admin);

        $permission = Permission::create(['name' => 'familiar.delete', 'descripcion' => 'Familiar: Eliminar'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'familiar.create', 'descripcion' => 'Familiar: Crear'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'familiar.edit', 'descripcion' => 'Familiar: Editar'])->syncRoles($admin);

        $permission = Permission::create(['name' => 'planilla.index', 'descripcion' => 'Planilla: Index'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'planilla.create', 'descripcion' => 'Planilla: Crear'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'planilla.exportarDetalle', 'descripcion' => 'Planilla: Exportar'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'planilla.anular', 'descripcion' => 'Planilla: Anular'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'planilla.cobrar', 'descripcion' => 'Planilla: Cobrar'])->syncRoles($admin);

        $permission = Permission::create(['name' => 'factura.index', 'descripcion' => 'Factura: Index'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'factura.anular', 'descripcion' => 'Factura: Anular'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'factura.aporte', 'descripcion' => 'Factura: Cobro Aporte'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'factura.show', 'descripcion' => 'Factura: Ver'])->syncRoles($admin);

        $permission = Permission::create(['name' => 'sifen.enviar', 'descripcion' => 'Sifen: Enviar'])->syncRoles($admin);

        $permission = Permission::create(['name' => 'entidad.index', 'descripcion' => 'Entidad: Index'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'entidad.firma', 'descripcion' => 'Entidad: Editar Firma'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'entidad.obligaciones', 'descripcion' => 'Entidad: Crear Obligaciones'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'entidad.obligacion_editar', 'descripcion' => 'Entidad: Editar Obligaciones'])->syncRoles($admin);

        $permission = Permission::create(['name' => 'establecimiento.index', 'descripcion' => 'Establecimiento: Index'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'establecimiento.create', 'descripcion' => 'Establecimiento: Crear'])->syncRoles($admin);
        $permission = Permission::create(['name' => 'establecimiento.edit', 'descripcion' => 'Establecimiento: Editar'])->syncRoles($admin);


        // $permission = Permission::create(['name' => 'habilitacion.estado_alumno', 'descripcion' => 'Habilitacion de Curso: Estado Alumno']);

    }

}
