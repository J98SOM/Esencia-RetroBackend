<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'productosxfactura';

        // If a foreign key exists on producto_id, drop it first
        $rows = DB::select('SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL', [$table, 'producto_id']);
        $fkName = null;
        if (! empty($rows)) {
            $fkName = $rows[0]->CONSTRAINT_NAME ?? null;
        }

        if ($fkName) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
        }

        // Modify column to allow NULL
        DB::statement("ALTER TABLE `{$table}` MODIFY `producto_id` BIGINT UNSIGNED NULL");

        // Recreate foreign key with ON DELETE SET NULL
        $newFk = $fkName ?: $table.'_producto_id_foreign';
        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$newFk}` FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE SET NULL");
    }

    public function down(): void
    {
        $table = 'productosxfactura';

        // Drop FK if present
        $rows = DB::select('SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL', [$table, 'producto_id']);
        $fkName = null;
        if (! empty($rows)) {
            $fkName = $rows[0]->CONSTRAINT_NAME ?? null;
        }
        if ($fkName) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
        }

        // Revert column to NOT NULL (no default)
        DB::statement("ALTER TABLE `{$table}` MODIFY `producto_id` BIGINT UNSIGNED NOT NULL");

        // Recreate foreign key with ON DELETE RESTRICT
        $newFk = $fkName ?: $table.'_producto_id_foreign';
        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$newFk}` FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE RESTRICT");
    }
};
