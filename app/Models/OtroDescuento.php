<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtroDescuento extends Model
{
    protected $guarded = [];

    public function asociados()
    {
        return $this->hasMany(OtroDescuentoAsociado::class);
    }
}
