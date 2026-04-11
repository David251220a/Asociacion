<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaAporte extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function planillas()
    {
        return $this->belongsTo(Planilla::class, 'planilla_id');
    }
}
