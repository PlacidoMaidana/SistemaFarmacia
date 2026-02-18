<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recetas', function (Blueprint $table) {
            if (!Schema::hasColumn('recetas', 'imagen')) {
                $table->string('imagen')->nullable()->after('observaciones');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recetas', function (Blueprint $table) {
            if (Schema::hasColumn('recetas', 'imagen')) {
                $table->dropColumn('imagen');
            }
        });
    }
};
