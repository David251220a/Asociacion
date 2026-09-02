<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamoPago extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function recibo()
    {
        return $this->belongsTo(
            Recibo::class,
            'recibo_id'
        );
    }

}
