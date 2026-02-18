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
     * Validar que tenga al menos una dispensación antes de eliminar
     */
    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($tratamiento) {
            // Eliminar dispensaciones asociadas en cascada
            $tratamiento->dispensaciones()->delete();
        });
    }
}
