<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function persona()
    {
        return $this->belongsTo(Persona::class); 
    }

    public function sifen()
    {
        return $this->hasOne(Sifen::class, 'factura_id');
    }

    public function tipo_documento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    public function forma_pagos()
    {
        return $this->hasMany(FacturaCobro::class);
    }

     public function tipodocumentofactura()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    public function tipoTransaccionFactura()
    {
        return $this->belongsTo(TipoTransaccion::class, 'tipo_transaccion_id');
    }

    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class, 'establecimiento_id');
    }

    public function scopeRechazadas($query)
    {
        return $query->whereRelation('sifen', 'sifen_estado', 'RECHAZADO');
    }
}
