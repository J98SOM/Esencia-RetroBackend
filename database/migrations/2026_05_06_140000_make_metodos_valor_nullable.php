<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Try to alter column to nullable; use raw statement for compatibility
        // MySQL / MariaDB
        try {
            DB::statement('ALTER TABLE `metodos_pago` MODIFY `valor` DECIMAL(14,2) NULL;');
        } catch (Throwable $e) {
            // If the alter fails (e.g., sqlite or missing privileges), attempt a Schema approach
            Schema::table('metodos_pago', function ($table) {
                $table->decimal('valor', 14, 2)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE `metodos_pago` MODIFY `valor` DECIMAL(14,2) NOT NULL DEFAULT 0;');
        } catch (Throwable $e) {
            Schema::table('metodos_pago', function ($table) {
                $table->decimal('valor', 14, 2)->default(0)->change();
            });
        }
    }
};
