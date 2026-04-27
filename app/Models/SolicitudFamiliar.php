<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudFamiliar extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tipo_familia()
    {
        return $this->belongsTo(TipoFamiliar::class, 'tipo_familiar');
    }

}
