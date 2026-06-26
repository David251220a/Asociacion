<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use App\Models\Persona;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function aporte()
    {
        $persona = Persona::where('documento', auth()->user()->documento)->firstOrFail();

        $asociado = Asociado::with([
            'persona',
            'tipo_asociado',
        ])->where('persona_id', $persona->id)->firstOrFail();

        $aportesUltimos = $asociado->aportes_activo()
        ->with('recibo')
        ->orderByDesc('fecha_aporte')
        ->orderByDesc('id')
        ->limit(6)
        ->get();

        $idsUltimos = $aportesUltimos->pluck('id');
        $totalAportado = $asociado->aportes_activo()->sum('aporte');
        $cantidadAportes = $asociado->aportes_activo()->count();

        $totalAnterior = $asociado->aportes_activo()
        ->whereNotIn('id', $idsUltimos)
        ->sum('aporte');

        $cantidadMeses = $asociado->aportes_activo()
        ->select('anio', 'mes')
        ->distinct()
        ->count();

        $anios = intdiv($cantidadMeses, 12);
        $meses = $cantidadMeses % 12;

        $antiguedad = '';

        if ($anios > 0) {
            $antiguedad .= $anios . ' año' . ($anios > 1 ? 's' : '');
        }

        if ($meses > 0) {
            if ($antiguedad != '') {
                $antiguedad .= ' y ';
            }

            $antiguedad .= $meses . ' mes' . ($meses > 1 ? 'es' : '');
        }

        if ($antiguedad == '') {
            $antiguedad = '0 meses';
        }

        $cantidadAnterior = $asociado->aportes_activo()
        ->whereNotIn('id', $idsUltimos)
        ->count();

        $ultimoAporte = $aportesUltimos->first();

        return view('aporte', compact(
            'asociado',
            'aportesUltimos',
            'totalAportado',
            'cantidadAportes',
            'totalAnterior',
            'cantidadAnterior',
            'ultimoAporte',
            'antiguedad'
        ));
    }
}
