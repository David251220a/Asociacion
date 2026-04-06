<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planilla extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function planillaDetalle()
    {
        return $this->hasMany(PlanillaDetalle::class);
    }

    public function tipoAsociado()
    {
        return $this->belongsTo(TipoAsociado::class);
    }
}
