<?php
// app/Models/Dispensacion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispensacion extends Model
{
    use HasFactory;
    
    protected $table = 'dispensaciones';
    protected $primaryKey = 'id_dispensacion';
    
    protected $fillable = [
        'tipo_origen', 'id_origen', 'id_interno',
        'id_medicamento', 'id_material', 'cantidad', 'unidad_medida',
        'fecha', 'hora', 'id_usuario', 'observaciones',
        'es_psicotropico', 'nro_lote', 'fecha_vencimiento'
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'decimal:2',
        'es_psicotropico' => 'boolean',
    ];

    // Relación polimórfica con origen (receta, tratamiento, suministro)
    public function origen()
    {
        $models = [
            'receta' => Receta::class,
            'tratamiento' => TratamientoCronico::class,
            'suministro' => MaterialesEnfermeria::class,
        ];

        $modelClass = $models[$this->tipo_origen] ?? null;
        
        if (!$modelClass) {
            return null;
        }

        return $this->belongsTo($modelClass, 'id_origen');
    }

    // Relaciones específicas por tipo
    public function receta()
    {
        return $this->belongsTo(Receta::class, 'id_origen', 'id_receta')
                    ->where('tipo_origen', 'receta');
    }

    public function tratamiento()
    {
        return $this->belongsTo(TratamientoCronico::class, 'id_origen', 'id_tratamiento')
                    ->where('tipo_origen', 'tratamiento');
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class, 'id_medicamento', 'id_medicamento');
    }

    public function material()
    {
        return $this->belongsTo(MaterialesEnfermeria::class, 'id_material', 'id_material');
    }

    public function interno()
    {
        return $this->belongsTo(Interno::class, 'id_interno', 'id_interno');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    // Scopes
    public function scopeDeReceta($query, $idReceta)
    {
        return $query->where('tipo_origen', 'receta')
                     ->where('id_origen', $idReceta);
    }

    public function scopeDeTratamiento($query, $idTratamiento)
    {
        return $query->where('tipo_origen', 'tratamiento')
                     ->where('id_origen', $idTratamiento);
    }

    public function scopeDeSuministro($query, $idSuministro)
    {
        return $query->where('tipo_origen', 'suministro')
                     ->where('id_origen', $idSuministro);
    }

    // Accesor para mostrar el tipo de item dispensado
    public function getItemDispensadoAttribute()
    {
        if ($this->id_medicamento) {
            return $this->medicamento ? $this->medicamento->nombre : 'Medicamento #' . $this->id_medicamento;
        }
        
        if ($this->id_material) {
            return $this->material ? $this->material->nombre : 'Material #' . $this->id_material;
        }
        
        return 'Sin item';
    }

    // Validación: debe tener medicamento O material, no ambos
    public static function boot()
    {
        parent::boot();

        static::saving(function ($dispensacion) {
            // Validar que tenga medicamento O material (XOR)
            $tieneMedicamento = !empty($dispensacion->id_medicamento);
            $tieneMaterial = !empty($dispensacion->id_material);
            
            if (!$tieneMedicamento && !$tieneMaterial) {
                throw new \Exception('La dispensación debe tener un medicamento o un material.');
            }
            
            if ($tieneMedicamento && $tieneMaterial) {
                throw new \Exception('La dispensación no puede tener medicamento y material simultáneamente.');
            }
        });

        // Registrar en auditoría antes de eliminar dispensaciones de psicotrópicos
        static::deleting(function ($dispensacion) {
            if ($dispensacion->es_psicotropico) {
                \App\Models\AuditoriaPsicotropicos::crearRegistroAuditoria(
                    $dispensacion, 
                    'Eliminación manual de dispensación',
                    'eliminacion'
                );
            }
        });
    }
}
