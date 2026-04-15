<?php

namespace App\Http\Controllers;

use App\Models\Entidad;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function mision()
    {
        $entidad = Entidad::find(1);
        $mision = $entidad->mision;
        $vision = $entidad->vision;
        return view('www.mision', compact('mision', 'vision'));
    }

    public function beneficios()
    {
        return view('www.beneficios');
    }

    public function contacto()
    {
        return view('www.contacto');
    }

    public function noticias()
    {
        return view('www.noticias');
    }

    public function noticias_show($noticias)
    {
        return view('www.noticias_show');
    }
}
