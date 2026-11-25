<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's permissions.
     */
    public function run(): void
    {
        $admin = Role::find(1);

        $permissions = [
            'rol.index' => 'Rol Index',
            'rol.create' => 'Rol Crear',
            'rol.edit' => 'Rol Editar',
            'rol.show' => 'Rol Ver',
            'rol.update' => 'Rol Actualizar',
            'rol.store' => 'Rol Guardar',
            'rol.delete' => 'Rol Eliminar',
            'usuario.index' => 'Usuario Index',
            'usuario.create' => 'Usuario Crear',
            'usuario.edit' => 'Usuario Editar',
            'usuario.show' => 'Usuario Ver',
            'usuario.update' => 'Usuario Actualizar',
            'usuario.store' => 'Usuario Guardar',
            'usuario.delete' => 'Usuario Eliminar',
            'asociado.index' => 'Asociado: Index',
            'asociado.create' => 'Asociado: Crear',
            'asociado.edit' => 'Asociado: Editar',
            'asociado.show' => 'Asociado: Ver',
            'ficha_medica.create' => 'Ficha Medica: Crear',
            'familiar.delete' => 'Familiar: Eliminar',
            'familiar.create' => 'Familiar: Crear',
            'familiar.edit' => 'Familiar: Editar',
            'planilla.index' => 'Planilla: Index',
            'planilla.create' => 'Planilla: Crear',
            'planilla.exportarDetalle' => 'Planilla: Exportar',
            'planilla.anular' => 'Planilla: Anular',
            'planilla.cobrar' => 'Planilla: Cobrar',
            'planilla.descuentos.index' => 'Otros descuentos: Ver índice',
            'planilla.descuentos.show' => 'Otros descuentos: Ver detalle',
            'planilla.descuentos.create' => 'Otros descuentos: Crear',
            'planilla.descuentos.edit' => 'Otros descuentos: Editar',
            'planilla.descuentos.destroy' => 'Otros descuentos: Eliminar',
            'planilla.descuentos.socios' => 'Otros descuentos: Gestionar socios',
            'factura.index' => 'Factura: Index',
            'factura.anular' => 'Factura: Anular',
            'factura.aporte' => 'Factura: Cobro Aporte',
            'factura.show' => 'Factura: Ver',
            'sifen.enviar' => 'Sifen: Enviar',
            'entidad.index' => 'Entidad: Index',
            'entidad.firma' => 'Entidad: Editar Firma',
            'entidad.obligaciones' => 'Entidad: Crear Obligaciones',
            'entidad.obligacion_editar' => 'Entidad: Editar Obligaciones',
            'entidad.actividades' => 'Entidad: Crear Actividad Economica',
            'entidad.actividades_editar' => 'Entidad: Editar Actividad Economica',
            'establecimiento.index' => 'Establecimiento: Index',
            'establecimiento.create' => 'Establecimiento: Crear',
            'establecimiento.edit' => 'Establecimiento: Editar',
            'recibo.index' => 'Recibo: Index',
            'recibo.show' => 'Recibo: Ver',
            'recibo.anular' => 'Recibo: Anular',
            'recibo.aporte' => 'Recibo: Aporte Individual',
            'recibo.varios' => 'Recibo: Donacion',
            'solicitud.index' => 'Solicitud: Index',
            'solicitud.show' => 'Solicitud: Ver',
            'miembros.index' => 'Miembros',
            'orden.index' => 'Orden Pago: Index',
            'orden.show' => 'Orden Pago: Ver',
            'orden.create' => 'Orden Pago: Crear',
            'orden.pago' => 'Orden Pago: Pagar',
            'orden.anular' => 'Orden Pago: Anular',
            'persona.index' => 'Persona: Index',
            'persona.create' => 'Persona: Crear',
            'persona.edit' => 'Persona: Editar',
            'resumen.index' => 'Resumen',
            'resumen.recalcular' => 'Recalcular Saldos',
            'solicitudes' => 'Solicitudes Todos',
            'solicitud.index_ayuda_social' => 'Solicitud: Ayuda Social',
            'solicitud.show_ayuda_social' => 'Solicitud: Ayuda Social Aprobacion/Rechazo',
            'consultas' => 'Consultas: Todos',
            'cobros' => 'Cobros: Todos',
            'parametro_general' => 'Parametros Generales',
            'entidad_soli.solicitud' => 'Entidad: Activar Solicitudes',
            'entidad_soli.solicitud_ayuda_social' => 'Entidad: Activar Ayuda Social',
            'actu_datos.index' => 'Solicitud: Datos Actualizacion',
            'actu_datos.show' => 'Solicitud: Datos Aprobacion o Rechazo',
            'solicitud.prestamo_emergencia' => 'Solicitud: Prestamo Emergencia',
            'solicitud.prestamo_emergencia_show' => 'Solicitud: Prestamo Emergencia Aprobacion/Rechazo',
            'prestamos.index' => 'Prestamos: Principal',
            'prestamos.show' => 'Prestamos: Detalle',
            'prestamos.contrato' => 'Prestamos: Contrato',
            'prestamos.descuentos' => 'Consultas: Descuentos de préstamos',
            'habilitacion.estado_alumno' => 'Habilitacion de Curso: Estado Alumno',
        ];

        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['descripcion' => $description]
            );

            if ($admin && ! $admin->hasPermissionTo($permission)) {
                $admin->givePermissionTo($permission);
            }
        }
    }
}
