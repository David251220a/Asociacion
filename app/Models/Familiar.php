<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Familiar extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tipo()
    {
        return $this->belongsTo(TipoFamiliar::class, 'tipo_familiar_id');
    }
}
