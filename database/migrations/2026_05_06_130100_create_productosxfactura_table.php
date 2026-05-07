<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productosxfactura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->text('descripcion')->nullable();
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio_unitario', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productosxfactura');
    }
};
