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
        Schema::create('auditoria_psicotropicos', function (Blueprint $table) {
            $table->id('id_auditoria');
            
            // Referencia a la dispensación original
            $table->unsignedBigInteger('id_dispensacion');
            
            // Información del origen de la dispensación
            $table->enum('tipo_origen', ['receta', 'tratamiento_cronicos', 'suministros_enfermeria']);
            $table->unsignedBigInteger('id_origen');
            
            // Información del interno y medicamento
            $table->unsignedBigInteger('id_interno');
            $table->unsignedBigInteger('id_medicamento');
            $table->decimal('cantidad', 10, 2);
            
            // Información temporal de la dispensación original
            $table->date('fecha_dispensacion');
            $table->time('hora_dispensacion');
            $table->unsignedBigInteger('id_usuario_dispenso');
            
            // Información de la eliminación/modificación
            $table->enum('tipo_accion', ['eliminacion', 'modificacion'])->default('eliminacion');
            $table->string('motivo_eliminacion');
            $table->datetime('fecha_eliminacion');
            $table->unsignedBigInteger('id_usuario_elimino');
            $table->text('observaciones_auditoria')->nullable();
            
            // Datos adicionales para trazabilidad
            $table->string('nro_lote')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('ip_eliminacion')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['id_medicamento', 'fecha_dispensacion']);
            $table->index(['id_interno', 'tipo_origen', 'id_origen']);
            $table->index(['fecha_eliminacion', 'tipo_accion']);
            $table->index(['id_usuario_dispenso']);
            $table->index(['id_usuario_elimino']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auditoria_psicotropicos');
    }
};