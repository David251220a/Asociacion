<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudActualizacionDatos extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function estadoSolicitud()
    {
        return $this->belongsTo(EstadoSolicitud::class,'estado_solicitud_id');
    }

    public function institucionActual()
    {
        return $this->belongsTo(Institucion::class,'institucion_municipal_id_actual');
    }

    public function institucionNueva()
    {
        return $this->belongsTo(Institucion::class,'institucion_municipal_id_nueva');
    }

}
