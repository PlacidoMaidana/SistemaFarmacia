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
        Schema::create('recepciones_central_items', function (Blueprint $table) {
            $table->id('id_item');
            
            // Relación con la cabecera de recepción
            $table->unsignedBigInteger('id_recepcion');
            
            // Tipo de item recibido (medicamento o material)
            $table->enum('tipo_item', ['MEDICAMENTO', 'MATERIAL']);
            
            // Referencias a medicamento o material (solo uno debe tener valor)
            $table->unsignedBigInteger('id_medicamento')->nullable();
            $table->unsignedBigInteger('id_material')->nullable();
            
            // Cantidad recibida
            $table->decimal('cantidad', 10, 2);
            
            // Información de lote y vencimiento
            $table->string('nro_lote')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            
            // Timestamp de creación
            $table->timestamps();
            
            // Foreign Key Constraints (comentadas para evitar problemas de tipo)
            // Se implementan a nivel de aplicación por ahora
            // $table->foreign('id_recepcion')->references('id_recepcion')->on('recepciones_central')
            //       ->onDelete('cascade')->onUpdate('cascade');
            
            // Índices para optimizar consultas
            $table->index(['id_recepcion'], 'idx_id_recepcion');
            $table->index(['tipo_item', 'id_medicamento'], 'idx_tipo_medicamento');
            $table->index(['tipo_item', 'id_material'], 'idx_tipo_material');
            $table->index(['nro_lote'], 'idx_nro_lote');
            $table->index(['fecha_vencimiento'], 'idx_fecha_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recepciones_central_items');
    }
};