<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtroDescuentoAsociado extends Model
{
    protected $guarded = [];

    public function descuento()
    {
        return $this->belongsTo(OtroDescuento::class, 'otro_descuento_id');
    }

    public function cuotas()
    {
        return $this->hasMany(OtroDescuentoCuota::class, 'otro_descuento_asociado_id');
    }

    public function asociado()
    {
        return $this->belongsTo(Asociado::class, 'asociado_id');
    }
}
