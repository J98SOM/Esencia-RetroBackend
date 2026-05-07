<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoXFactura extends Model
{
    use HasFactory;

    protected $table = 'productosxfactura';

    protected $fillable = [
        'producto_id', 'factura_id', 'cantidad', 'precio_unitario', 'descripcion',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
