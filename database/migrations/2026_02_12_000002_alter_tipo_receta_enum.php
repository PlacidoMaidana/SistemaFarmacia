<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Step 1: First, expand tipo_receta to VARCHAR(50) to allow all current values
        DB::statement("ALTER TABLE `recetas` MODIFY `tipo_receta` VARCHAR(50) NULL;");
        
        // Step 2: Clean/replace invalid values (any value longer than the enum values)
        // Use CASE to safely map values
        DB::statement("UPDATE `recetas` SET `tipo_receta` = CASE 
            WHEN `tipo_receta` IN ('electronica','papel','cronica','aguda','archivada','anulada','pendiente','repetible','unica') THEN `tipo_receta`
            ELSE 'pendiente'
        END WHERE `tipo_receta` IS NOT NULL;");
        
        // Step 3: Now convert to ENUM
        DB::statement("ALTER TABLE `recetas` MODIFY `tipo_receta` ENUM('electronica','papel','cronica','aguda','archivada','anulada','pendiente','repetible','unica') NOT NULL DEFAULT 'pendiente';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to a VARCHAR(50) if rolling back
        DB::statement("ALTER TABLE `recetas` MODIFY `tipo_receta` VARCHAR(50) NULL;");
    }
};
