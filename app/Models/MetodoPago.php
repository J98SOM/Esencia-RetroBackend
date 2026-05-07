<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $fillable = [
        'factura_id', 'metodo', 'valor',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }
}
