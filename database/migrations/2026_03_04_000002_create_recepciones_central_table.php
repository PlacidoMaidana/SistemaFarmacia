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
        Schema::create('recepciones_central', function (Blueprint $table) {
            $table->id('id_recepcion');
            
            // Datos del remito/acta desde Farmacia Central
            $table->string('nro_remito');
            $table->date('fecha_recepcion');
            
            // Estado del proceso
            $table->enum('estado', ['BORRADOR', 'CONFIRMADA', 'ANULADA'])
                  ->default('BORRADOR');
            
            // Unidad de destino (opcional, si el sistema maneja múltiples unidades)
            $table->unsignedBigInteger('unidad_id')->nullable();
            
            // Usuario que registra la recepción
            $table->unsignedBigInteger('id_usuario');
            
            // Observaciones generales
            $table->text('observaciones')->nullable();
            
            // Timestamps estándar de Laravel
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['estado'], 'idx_estado');
            $table->index(['fecha_recepcion'], 'idx_fecha_recepcion');
            $table->index(['nro_remito'], 'idx_nro_remito');
            $table->index(['id_usuario'], 'idx_usuario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recepciones_central');
    }
};