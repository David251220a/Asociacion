<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function forma_pagos()
    {
        return $this->hasMany(ReciboPago::class);
    }

    public function tipo_recibo()
    {
        return $this->belongsTo(TipoRecibo::class);
    }

}
