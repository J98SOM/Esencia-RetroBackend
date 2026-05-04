<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $fillable = [
        'nombre',
        'capacidad',
    ];

    protected $casts = [
        'capacidad' => 'integer',
    ];
}
