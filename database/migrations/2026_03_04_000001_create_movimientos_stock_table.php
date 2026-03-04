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
        Schema::create('movimientos_stock', function (Blueprint $table) {
            $table->id('id_movimiento');
            
            // Tipo de item afectado (medicamento o material)
            $table->enum('tipo_item', ['MEDICAMENTO', 'MATERIAL']);
            
            // Referencias a medicamento o material (solo uno debe tener valor)
            $table->unsignedBigInteger('id_medicamento')->nullable();
            $table->unsignedBigInteger('id_material')->nullable();
            
            // Tipo de movimiento
            $table->enum('tipo_movimiento', [
                'INGRESO_CENTRAL', 
                'EGRESO_DISPENSACION', 
                'REVERSA_DISPENSACION', 
                'AJUSTE', 
                'BAJA_VENCIMIENTO'
            ]);
            
            // Cantidad y saldos
            $table->decimal('cantidad', 10, 2);
            $table->integer('saldo_anterior');
            $table->integer('saldo_nuevo');
            
            // Información de lote y vencimiento (opcional)
            $table->string('nro_lote')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            
            // Origen del movimiento (para trazabilidad)
            $table->enum('origen_tipo', ['DISPENSACION', 'RECEPCION_CENTRAL', 'AJUSTE']);
            $table->unsignedBigInteger('origen_id');
            
            // Usuario que ejecuta el movimiento
            $table->unsignedBigInteger('id_usuario');
            
            // Fecha y hora del movimiento
            $table->date('fecha');
            $table->time('hora');
            
            // Observaciones opcionales
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            
            // Foreign Key Constraints - Se agregarán posteriormente debido a posibles diferencias en tipos de datos
            // $table->foreign('id_medicamento')->references('id_medicamento')->on('medicamentos')
            //       ->onDelete('restrict')->onUpdate('cascade');
            // $table->foreign('id_material')->references('id_material')->on('materiales_enfermeria')
            //       ->onDelete('restrict')->onUpdate('cascade');
            // $table->foreign('id_usuario')->references('id')->on('users')
            //       ->onDelete('restrict')->onUpdate('cascade');
            
            // Índices para optimizar consultas
            $table->index(['tipo_item', 'id_medicamento'], 'idx_tipo_medicamento');
            $table->index(['tipo_item', 'id_material'], 'idx_tipo_material');
            $table->index(['origen_tipo', 'origen_id'], 'idx_origen');
            $table->index(['fecha'], 'idx_fecha');
            $table->index(['tipo_movimiento'], 'idx_tipo_movimiento');
            $table->index(['id_usuario'], 'idx_usuario');
            
            // Constraint para verificar que solo uno de id_medicamento o id_material tenga valor
            // Se implementa a nivel de aplicación en el modelo
        });
        
        // Agregar constraint de check a nivel de base de datos (opcional, según el motor)
        // Para MySQL, implementamos la validación XOR a nivel de aplicación en el modelo
        // DB::statement('ALTER TABLE movimientos_stock ADD CONSTRAINT chk_xor_medicamento_material 
        //               CHECK ((id_medicamento IS NOT NULL AND id_material IS NULL) 
        //                   OR (id_medicamento IS NULL AND id_material IS NOT NULL))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimientos_stock');
    }
};