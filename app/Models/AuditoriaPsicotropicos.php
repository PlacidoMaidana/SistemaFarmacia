<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaPsicotropicos extends Model
{
    protected $table = 'auditoria_psicotropicos';
    protected $primaryKey = 'id_auditoria';
    
    protected $fillable = [
        'id_dispensacion',
        'tipo_origen',
        'id_origen',
        'id_interno',
        'id_medicamento',
        'cantidad',
        'fecha_dispensacion',
        'hora_dispensacion',
        'id_usuario_dispenso',
        'tipo_accion',
        'motivo_eliminacion',
        'fecha_eliminacion',
        'id_usuario_elimino',
        'observaciones_auditoria',
        'nro_lote',
        'fecha_vencimiento',
        'ip_eliminacion',
    ];

    protected $casts = [
        'fecha_dispensacion' => 'date',
        'fecha_eliminacion' => 'datetime',
        'cantidad' => 'decimal:2',
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

    public function usuarioDispensador()
    {
        return $this->belongsTo(User::class, 'id_usuario_dispenso', 'id');
    }

    public function usuarioEliminador()
    {
        return $this->belongsTo(User::class, 'id_usuario_elimino', 'id');
    }

    /**
     * Método estático para crear registro de auditoría
     */
    public static function crearRegistroAuditoria(Dispensacion $dispensacion, $motivo = 'Eliminación manual', $tipoAccion = 'eliminacion')
    {
        if (!$dispensacion->es_psicotropico) {
            return; // No crear auditoría para medicamentos no psicotrópicos
        }

        return self::create([
            'id_dispensacion' => $dispensacion->id_dispensacion,
            'tipo_origen' => $dispensacion->tipo_origen,
            'id_origen' => $dispensacion->id_origen,
            'id_interno' => $dispensacion->id_interno,
            'id_medicamento' => $dispensacion->id_medicamento,
            'cantidad' => $dispensacion->cantidad,
            'fecha_dispensacion' => $dispensacion->fecha,
            'hora_dispensacion' => $dispensacion->hora,
            'id_usuario_dispenso' => $dispensacion->id_usuario,
            'tipo_accion' => $tipoAccion,
            'motivo_eliminacion' => $motivo,
            'fecha_eliminacion' => now(),
            'id_usuario_elimino' => auth()->id() ?? 0,
            'observaciones_auditoria' => "Dispensación #{$dispensacion->id_dispensacion} - {$motivo}",
            'nro_lote' => $dispensacion->nro_lote,
            'fecha_vencimiento' => $dispensacion->fecha_vencimiento,
            'ip_eliminacion' => request()->ip(),
        ]);
    }

    /**
     * Scopes para consultas comunes
     */
    public function scopePorFechaDispensacion($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_dispensacion', [$fechaInicio, $fechaFin]);
    }

    public function scopePorFechaEliminacion($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_eliminacion', [$fechaInicio, $fechaFin]);
    }

    public function scopePorMedicamento($query, $idMedicamento)
    {
        return $query->where('id_medicamento', $idMedicamento);
    }

    public function scopePorInterno($query, $idInterno)
    {
        return $query->where('id_interno', $idInterno);
    }
}