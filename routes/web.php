<?php

use App\Http\Controllers\AsociadoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EntidadController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\FamiliarController;
use App\Http\Controllers\FichaMedicaController;
use App\Http\Controllers\GrupoUsuarioController;
use App\Http\Controllers\LimpiarController;
use App\Http\Controllers\MiembroController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PlanillaController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\SifenController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [WebController::class, 'index'])->name('inicio');
Route::get('/mision-vision', [WebController::class, 'mision'])->name('mision');
Route::get('/beneficios', [WebController::class, 'beneficios'])->name('beneficios');
Route::get('/contactos', [WebController::class, 'contacto'])->name('contacto');
Route::get('/noticias', [WebController::class, 'noticias'])->name('noticias');
Route::get('/noticias/{noticias}/ver', [WebController::class, 'noticias_show'])->name('noticias.show');
Route::get('/asociarse', [WebController::class, 'asociarse'])->name('asociarse');
Route::get('/limpiar', [LimpiarController::class, 'limpiar'])->name('limpiar');

Route::get('/logout', [LoginController::class, 'logout']);

Auth::routes();

Route::group([
    'middleware' => 'auth',
], function(){
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::resource('/asociados', AsociadoController::class)->names('asociado');
    Route::resource('/users', UsuarioController::class)->names('user');
    Route::resource('/roles', GrupoUsuarioController::class)->names('role');
    Route::get('/permiso-crear', [GrupoUsuarioController::class, 'permiso_crear'])->name('role.permiso_crear');
    Route::post('/permiso-crear', [GrupoUsuarioController::class, 'permiso_crear_post'])->name('role.permiso_crear_post');

    Route::get('/distritos/{departamento}', [AsociadoController::class, 'getDistritos']);
    Route::get('/ciudades/{id}', [AsociadoController::class, 'getCiudades']);

    Route::get('/ficha_medica/{asociado}/create', [FichaMedicaController::class, 'create'])->name('ficha_medica.create');
    Route::post('/ficha_medica/{asociado}/create', [FichaMedicaController::class, 'store'])->name('ficha_medica.store');

    Route::get('/familiar/{persona}/crear', [FamiliarController::class, 'create'])->name('familiar.create');
    Route::post('/familiar/{persona}/crear', [FamiliarController::class, 'store'])->name('familiar.store');
    Route::get('/familiar/{familiar}/editar', [FamiliarController::class, 'edit'])->name('familiar.edit');
    Route::post('/familiar/{familiar}/editar', [FamiliarController::class, 'update'])->name('familiar.update');
    Route::post('/familiar/{familiar}/eliminar', [FamiliarController::class, 'delete'])->name('familiar.delete');

    Route::get('/planilla', [PlanillaController::class, 'index'])->name('planilla.index');
    Route::get('/planilla/create', [PlanillaController::class, 'create'])->name('planilla.create');
    Route::get('/planilla/{planilla}/exportar-detalle', [PlanillaController::class, 'exportarDetalle'])->name('planilla.exportarDetalle');
    Route::post('/planilla/{planilla}/anular', [PlanillaController::class, 'anular'])->name('planilla.anular');
    Route::get('/planilla/{planilla}/cobrar', [PlanillaController::class, 'cobrar'])->name('planilla.cobrar');

    Route::get('/factura', [FacturaController::class, 'index'])->name('factura.index');
    Route::get('/factura/{factura}/ver', [FacturaController::class, 'show'])->name('factura.show');
    Route::post('/factura/{factura}/anular', [FacturaController::class, 'anular'])->name('factura.anular');

    Route::post('/sifen/{factura}/enviar', [SifenController::class, 'enviar'])->name('sifen.enviar');

    Route::get('/pdf/{factura}/factura', [PdfController::class, 'factura'])->name('pdf.factura');
    Route::get('/pdf/{recibo}/recibo', [PdfController::class, 'recibo'])->name('pdf.recibo');

    Route::get('/entidad', [EntidadController::class, 'index'])->name('entidad.index');
    Route::get('/entidad/firma', [EntidadController::class, 'firma'])->name('entidad.firma');
    Route::post('/entidad/firma', [EntidadController::class, 'firma_post'])->name('entidad.firma_post');
    Route::get('/entidad/obligaciones/agregar', [EntidadController::class, 'obligaciones'])->name('entidad.obligaciones');
    Route::post('/entidad/obligaciones/agregar', [EntidadController::class, 'obligaciones_post'])->name('entidad.obligaciones_post');
    Route::get('/entidad/obligaciones/{obligaciones}/editar', [EntidadController::class, 'obligacion_editar'])->name('entidad.obligacion_editar');
    Route::post('/entidad/obligaciones/{obligaciones}/editar', [EntidadController::class, 'obligacion_editar_post'])->name('entidad.obligacion_editar_post');
    Route::get('/entidad/actividades/agregar', [EntidadController::class, 'actividades'])->name('entidad.actividades');
    Route::post('/entidad/actividades/agregar', [EntidadController::class, 'actividades_post'])->name('entidad.actividades_post');
     Route::get('/entidad/{actividadEconomica}/actividades/agregar', [EntidadController::class, 'actividades_editar'])->name('entidad.actividades_editar');
    Route::post('/entidad/{actividadEconomica}/actividades/agregar', [EntidadController::class, 'actividades_editar_post'])->name('entidad.actividades_editar_post');


    Route::get('/establecimiento', [EstablecimientoController::class, 'index'])->name('establecimiento.index');
    Route::get('/establecimiento/crear', [EstablecimientoController::class, 'create'])->name('establecimiento.create');
    Route::post('/establecimiento/crear', [EstablecimientoController::class, 'store'])->name('establecimiento.store');
    Route::get('/establecimiento/{establecimiento}/editar', [EstablecimientoController::class, 'edit'])->name('establecimiento.edit');
    Route::post('/establecimiento/{establecimiento}/editar', [EstablecimientoController::class, 'update'])->name('establecimiento.update');

    Route::get('/recibo', [ReciboController::class, 'index'])->name('recibo.index');
    Route::get('/recibo/{recibo}/ver', [ReciboController::class, 'show'])->name('recibo.show');
    Route::post('/recibo/{recibo}/anular', [ReciboController::class, 'anular'])->name('recibo.anular');
    Route::get('/recibo/cobro-aporte/individual', [ReciboController::class, 'aporte'])->name('recibo.aporte');
    Route::get('/recibo/cobro-varios', [ReciboController::class, 'varios'])->name('recibo.varios');

    Route::get('/solicitud', [SolicitudController::class, 'index'])->name('solicitud.index');
    Route::get('/solicitud/{solicitud}/aprobacion-o-rechazo', [SolicitudController::class, 'show'])->name('solicitud.show');
    Route::post('/solicitud/{solicitud}/aprobacion-o-rechazo', [SolicitudController::class, 'store'])->name('solicitud.store');
    Route::get('/solicitud/{solicitud}/imprimir', [SolicitudController::class, 'imprimir'])->name('solicitud.imprimir');

    Route::get('/miembros', [MiembroController::class, 'index'])->name('miembros.index');
    Route::post('/miembros/crear', [MiembroController::class, 'store'])->name('miembros.store');
    Route::post('/miembros/update', [MiembroController::class, 'update'])->name('miembros.update');
    Route::get('/miembros/{id}/presente', [MiembroController::class, 'cambiarPresente'])->name('miembros.cambiarPresente');


});
