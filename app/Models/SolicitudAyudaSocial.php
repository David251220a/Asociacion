<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

class SolicitudAyudaSocial extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function estadoSolicitud()
    {
        return $this->belongsTo(EstadoSolicitud::class);
    }

    public function orden_pago()
    {
        return $this->belongsTo(OrdenPago::class, 'orden_pago_id');
    }
}
