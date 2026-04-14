<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Establecimiento extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function entidad()
    {
        return $this->belongsTo(Entidad::class);
    }


    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class);
    }
}
