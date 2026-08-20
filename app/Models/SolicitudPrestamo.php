<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudPrestamo extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function detalles()
    {
        return $this->hasMany(SolicitudPrestamoDetalle::class,'solicitud_prestamo_id');
    }

    public function estadoSolicitud()
    {
        return $this->belongsTo( EstadoSolicitud::class,'estado_solicitud_id');
    }

    public function ordenPago()
    {
        return $this->belongsTo(OrdenPago::class,'orden_pago_id');
    }

}
