<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiembroPlanilla extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function detalles()
    {
        return $this->hasMany(MiembroPlanillaDetalle::class,'miembro_planilla_id');
    }

    public function ordenPago()
    {
        return $this->belongsTo(OrdenPago::class,'orden_pago_id');
    }

}
