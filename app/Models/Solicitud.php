<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tipo_asociado()
    {
        return $this->belongsTo(TipoAsociado::class);
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function sexo()
    {
        return $this->belongsTo(Sexo::class);
    }

    public function estado_civil()
    {
        return $this->belongsTo(EstadoCivil::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class);
    }

    public function tipo_vivienda()
    {
        return $this->belongsTo(TipoVivienda::class);
    }

    public function familiares()
    {
        return $this->hasMany(SolicitudFamiliar::class);
    }

    public function ficha_medica()
    {
        return $this->hasOne(SolicitudFichaMedica::class);
    }

}
