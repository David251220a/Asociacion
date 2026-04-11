<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaCobro extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function forma_cobro()
    {
        return $this->belongsTo(FormaCobro::class);
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class);
    }
}
