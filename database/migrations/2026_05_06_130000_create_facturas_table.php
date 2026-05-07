<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->default('pos'); // 'pos' or 'evento'
            $table->string('numero_orden')->nullable();

            // Cliente
            $table->string('persona')->nullable();
            $table->string('nit')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('ciudad')->nullable();

            $table->string('orden_compra')->nullable();
            $table->text('observaciones')->nullable();

            // Relación con mesa
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->nullOnDelete();

            $table->string('estatus')->default('pendiente');

            $table->decimal('monto_total', 14, 2)->default(0);
            $table->decimal('cambio', 14, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
