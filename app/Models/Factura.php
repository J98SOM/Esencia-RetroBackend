<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    protected $fillable = [
        'tipo', 'numero_orden', 'fecha', 'persona', 'nit', 'direccion', 'telefono', 'ciudad',
        'orden_compra', 'observaciones', 'mesa_id', 'estatus', 'monto_total', 'cambio',
    ];

    public function productos()
    {
        return $this->hasMany(ProductoXFactura::class, 'factura_id');
    }

    public function metodosPago()
    {
        return $this->hasMany(MetodoPago::class, 'factura_id');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }
}
