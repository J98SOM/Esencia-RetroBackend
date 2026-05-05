<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventarios';

    protected $fillable = [
        'nombre',
        'producto_id',
        'stock_inicial',
        'stock_minimo',
        'unidad_medida',
        'descuento_inventario',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
