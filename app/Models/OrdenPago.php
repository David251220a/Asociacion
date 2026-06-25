<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenPago extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tipo_egreso()
    {
        return $this->belongsTo(TipoEgreso::class);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function pagos()
    {
        return $this->hasMany(OrdenPagoPago::class);
    }

    public function detalles()
    {
        return $this->hasMany(OrdenPagoDetalle::class);
    }
}
