<?php

use App\Http\Controllers\AsociadoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FamiliarController;
use App\Http\Controllers\FichaMedicaController;
use App\Http\Controllers\PlanillaController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/logout', [LoginController::class, 'logout']);

Auth::routes();

Route::group([
    'middleware' => 'auth',
], function(){
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::resource('/asociados', AsociadoController::class)->names('asociado');

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

    
});
