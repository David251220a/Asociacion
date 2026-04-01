<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use Illuminate\Http\Request;

class FichaMedicaController extends Controller
{
    public function create(Asociado $asociado)
    {
        return view('asociados.ficha');
    }
}
