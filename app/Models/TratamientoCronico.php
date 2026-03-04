<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TratamientoCronico extends Model
{
    use HasFactory;

    protected $table = 'tratamientos_cronicos';
    protected $primaryKey = 'id_tratamiento';
    
    protected $fillable = [
        'id_interno',
        'diagnostico',
        'id_medicamento',
        'dosis',
        'frecuencia',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Relaciones
     */
    public function interno()
    {
        return $this->belongsTo(Interno::class, 'id_interno', 'id_interno');
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class, 'id_medicamento', 'id_medicamento');
    }

    public function dispensaciones()
    {
        return $this->hasMany(Dispensacion::class, 'id_origen', 'id_tratamiento')
                    ->where('tipo_origen', 'tratamiento');
    }

    /**
     * Validar que tenga al menos una dispensación
     */
    protected static function booted()
    {
        parent::booted();

        // Validación para actualización: un tratamiento editado debe tener al menos una dispensación
        static::updating(function ($model) {
            $dispensacionesCount = $model->dispensaciones()->count();
            if ($dispensacionesCount === 0) {
                throw new \Exception('No se puede guardar el tratamiento. Debe tener al menos una dispensación registrada.');
            }
        });

        static::deleting(function ($tratamiento) {
            // Crear registros de auditoría para psicotrópicos antes de eliminar
            $dispensacionesPsicotropicas = $tratamiento->dispensaciones()
                ->where('es_psicotropico', 1)
                ->get();

            foreach ($dispensacionesPsicotropicas as $dispensacion) {
                \App\Models\AuditoriaPsicotropicos::crearRegistroAuditoria(
                    $dispensacion,
                    'Eliminación por eliminación de tratamiento #' . $tratamiento->id_tratamiento,
                    'eliminacion'
                );
            }

            // Eliminar dispensaciones asociadas en cascada
            $tratamiento->dispensaciones()->delete();
        });
    }
}
