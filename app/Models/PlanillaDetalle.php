<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanillaDetalle extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function asociado()
    {
        return $this->belongsTo(Asociado::class);
    }
}
