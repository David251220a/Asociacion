<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function persona()
    {
        return $this->belongsTo(Persona::class,'persona_id');
    }

    public function tipoPrestamo()
    {
        return $this->belongsTo(TipoPrestamo::class,'tipo_prestamo_id');
    }

    public function detalles()
    {
        return $this->hasMany(PrestamoDetalle::class,'prestamo_id');
    }

    public function ordenPago()
    {
        return $this->belongsTo(OrdenPago::class,'orden_pago_id');
    }

    public function solicitud()
    {
        return $this->hasOne(SolicitudPrestamo::class,'prestamo_id');
    }
}
