<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenPagoPago extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function banco()
    {
        return $this->belongsTo(Banco::class);
    }

    public function forma()
    {
        return $this->belongsTo(FormaCobro::class, 'forma_cobro_id');
    }
}
