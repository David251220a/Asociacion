<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaCobroController extends Controller
{
    public function show(Factura $factura)
    {
        return view('factura.show');
    }
}
