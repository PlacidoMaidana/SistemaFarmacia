<?php
// app/Models/Receta.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory;

    protected $table = 'recetas';
    protected $primaryKey = 'id_receta';
    protected $fillable = [
        'id_interno', 'id_medico', 'id_usuario', 
        'fecha_emision', 'numero_receta', 'tipo_receta', 'observaciones', 'imagen'
    ];
    protected $appends = ['internoNombre', 'medicoNombre', 'usuarioNombre'];

    public function interno()
    {
        return $this->belongsTo(Interno::class, 'id_interno', 'id_interno');
    }
    
    public function getInternoNombreAttribute()
    {
        // Cargar la relación si no está cargada
        if (!$this->relationLoaded('interno')) {
            $this->load('interno');
        }
        
        if ($this->interno) {
            return $this->interno->nombre_y_apellido ?? $this->id_interno;
        }
        
        return $this->id_interno;
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'id_medico', 'id_medico');
    }
    
    public function getMedicoNombreAttribute()
    {
        // Cargar la relación si no está cargada
        if (!$this->relationLoaded('medico')) {
            $this->load('medico');
        }
        
        if ($this->medico) {
            return $this->medico->nombre_y_apellido ?? $this->id_medico;
        }
        
        return $this->id_medico;
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
    
    public function getUsuarioNombreAttribute()
    {
        // Cargar la relación si no está cargada
        if (!$this->relationLoaded('usuario')) {
            $this->load('usuario');
        }
        
        if ($this->usuario) {
            return $this->usuario->name ?? $this->id_usuario;
        }
        
        return $this->id_usuario;
    }
    public function dispensaciones()
    {
        return $this->hasMany(Dispensacion::class, 'id_origen', 'id_receta')
                    ->where('tipo_origen', 'receta');
    }


    // app/Models/Receta.php

    public function getVerDispensacionesAttribute()
    {
        return '<a href="/recetas/' . $this->id_receta . '/dispensaciones" class="btn btn-primary btn-sm">' .
               '<i class="voyager-list"></i> Ver Dispensaciones</a>';
    }

    protected static function booted()
    {
        parent::booted();

        static::retrieved(function ($model) {
            // Eager load relations if not already loaded
            if (!$model->relationLoaded('interno')) {
                $model->loadMissing('interno');
            }
            if (!$model->relationLoaded('medico')) {
                $model->loadMissing('medico');
            }
            if (!$model->relationLoaded('usuario')) {
                $model->loadMissing('usuario');
            }
        });

        static::saving(function ($model) {
            try {
                if (request()->hasFile('imagen')) {
                    $file = request()->file('imagen');
                    $path = $file->store('recetas', 'public');
                    $model->imagen = $path;
                }
            } catch (\Exception $e) {
                // no-op: if storing fails, ignore so save can continue (you may log in future)
            }
        });

        // Validación para actualización: una receta editada debe tener al menos una dispensación
        static::updating(function ($model) {
            $dispensacionesCount = $model->dispensaciones()->count();
            if ($dispensacionesCount === 0) {
                throw new \Exception('No se puede guardar la receta. Debe tener al menos una dispensación registrada.');
            }
        });

        // Eliminar dispensaciones en cascada al eliminar receta
        static::deleting(function ($model) {
            // Crear registros de auditoría para psicotrópicos antes de eliminar
            $dispensacionesPsicotropicas = $model->dispensaciones()
                ->where('es_psicotropico', 1)
                ->get();

            foreach ($dispensacionesPsicotropicas as $dispensacion) {
                \App\Models\AuditoriaPsicotropicos::crearRegistroAuditoria(
                    $dispensacion,
                    'Eliminación por eliminación de receta #' . $model->id_receta,
                    'eliminacion'
                );
            }

            // Eliminar todas las dispensaciones asociadas
            $model->dispensaciones()->delete();
        });
    }
}
