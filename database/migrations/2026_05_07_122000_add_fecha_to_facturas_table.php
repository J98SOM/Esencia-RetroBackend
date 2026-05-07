<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('facturas', 'fecha')) {
            Schema::table('facturas', function (Blueprint $table) {
                $table->date('fecha')->nullable()->after('numero_orden');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('facturas', 'fecha')) {
            Schema::table('facturas', function (Blueprint $table) {
                $table->dropColumn('fecha');
            });
        }
    }
};
