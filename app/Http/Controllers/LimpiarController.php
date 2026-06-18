<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;


class LimpiarController extends Controller
{
    public function limpiar(){

        Artisan::call('config:clear');
        Artisan::call('config:cache');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        // Artisan::call('storage:link');
        // Artisan::call('migrate', ['--force' => true]);
        // Artisan::call('db:seed', ['--force' => true]);
        return 'Proceso de Limpiado correctamente.';
    }
}
