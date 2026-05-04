<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'categoria',
        'precio',
        'imagen_url',
        'imagen_public_id',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];
}
