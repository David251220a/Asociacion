<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function familiares()
    {
        return $this->hasMany(Familiar::class);
    }

    public function asociado()
    {
        return $this->hasOne(Asociado::class);
    }
}
