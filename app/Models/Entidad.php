<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function actividades()
    {
        return $this->hasMany(ActividadEconomica::class, 'entidad_id');
    }

    public function obligaciones()
    {
        return $this->hasMany(Obligaciones::class, 'entidad_id');
    }

    public function departamentos()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function distritos()
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }

    public function ciudades()
    {
        return $this->belongsTo(Ciudad::class, 'distrito_id');
    }
    
}
