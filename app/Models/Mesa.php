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

    public function facturas()
    {
        return $this->hasMany(\App\Models\Factura::class, 'mesa_id');
    }

    /**
     * Latest factura (one) for quick access to current status
     */
    public function latestFactura()
    {
        // Use id ordering to avoid relying on created_at being present
        return $this->hasOne(\App\Models\Factura::class, 'mesa_id')->latestOfMany('id');
    }
}
