<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoStock extends Model
{
    use HasFactory;

    protected $table = 'movimientos_stock';
    protected $primaryKey = 'id_movimiento';

    protected $fillable = [
        'tipo_item',
        'id_medicamento', 
        'id_material',
        'tipo_movimiento',
        'cantidad',
        'saldo_anterior',
        'saldo_nuevo',
        'nro_lote',
        'fecha_vencimiento',
        'origen_tipo',
        'origen_id',
        'id_usuario',
        'fecha',
        'hora',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_vencimiento' => 'date',
        'cantidad' => 'decimal:2',
    ];

    // Constantes para tipos de item
    const TIPO_MEDICAMENTO = 'MEDICAMENTO';
    const TIPO_MATERIAL = 'MATERIAL';

    // Constantes para tipos de movimiento
    const INGRESO_CENTRAL = 'INGRESO_CENTRAL';
    const EGRESO_DISPENSACION = 'EGRESO_DISPENSACION';
    const REVERSA_DISPENSACION = 'REVERSA_DISPENSACION';
    const AJUSTE = 'AJUSTE';
    const BAJA_VENCIMIENTO = 'BAJA_VENCIMIENTO';

    // Constantes para tipos de origen
    const ORIGEN_DISPENSACION = 'DISPENSACION';
    const ORIGEN_RECEPCION_CENTRAL = 'RECEPCION_CENTRAL';
    const ORIGEN_AJUSTE = 'AJUSTE';

    /**
     * Relación con medicamento
     */
    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'id_medicamento', 'id_medicamento');
    }

    /**
     * Relación con material de enfermería
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialesEnfermeria::class, 'id_material', 'id_material');
    }

    /**
     * Relación con usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Relación polimórfica con el origen según el tipo
     */
    public function origen()
    {
        switch ($this->origen_tipo) {
            case self::ORIGEN_DISPENSACION:
                return $this->belongsTo(Dispensacion::class, 'origen_id', 'id_dispensacion');
            // Aquí se agregarán más casos cuando se implementen recepciones y ajustes
            default:
                return null;
        }
    }

    /**
     * Getter para obtener el item afectado (medicamento o material)
     */
    public function getItemAfectadoAttribute()
    {
        if ($this->tipo_item === self::TIPO_MEDICAMENTO && $this->medicamento) {
            return $this->medicamento->nombre;
        }
        
        if ($this->tipo_item === self::TIPO_MATERIAL && $this->material) {
            return $this->material->nombre;
        }
        
        return 'Item no encontrado';
    }

    /**
     * Getter para mostrar descripción del movimiento
     */
    public function getDescripcionMovimientoAttribute()
    {
        $descripciones = [
            self::INGRESO_CENTRAL => 'Ingreso desde Farmacia Central',
            self::EGRESO_DISPENSACION => 'Egreso por Dispensación',
            self::REVERSA_DISPENSACION => 'Reversa de Dispensación',
            self::AJUSTE => 'Ajuste de Stock',
            self::BAJA_VENCIMIENTO => 'Baja por Vencimiento'
        ];

        return $descripciones[$this->tipo_movimiento] ?? 'Movimiento desconocido';
    }

    /**
     * Scope para filtrar por tipo de item
     */
    public function scopePorTipoItem($query, $tipo)
    {
        return $query->where('tipo_item', $tipo);
    }

    /**
     * Scope para filtrar por medicamento
     */
    public function scopePorMedicamento($query, $idMedicamento)
    {
        return $query->where('tipo_item', self::TIPO_MEDICAMENTO)
                     ->where('id_medicamento', $idMedicamento);
    }

    /**
     * Scope para filtrar por material
     */
    public function scopePorMaterial($query, $idMaterial)
    {
        return $query->where('tipo_item', self::TIPO_MATERIAL)
                     ->where('id_material', $idMaterial);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopePorFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope para filtrar por tipo de movimiento
     */
    public function scopePorTipoMovimiento($query, $tipo)
    {
        return $query->where('tipo_movimiento', $tipo);
    }

    /**
     * Validaciones al guardar
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($movimiento) {
            // Validar que tenga medicamento O material (XOR)
            $tieneMedicamento = !empty($movimiento->id_medicamento);
            $tieneMaterial = !empty($movimiento->id_material);
            
            if (!$tieneMedicamento && !$tieneMaterial) {
                throw new \Exception('El movimiento de stock debe referenciar un medicamento o un material.');
            }
            
            if ($tieneMedicamento && $tieneMaterial) {
                throw new \Exception('El movimiento de stock no puede referenciar medicamento y material simultáneamente.');
            }

            // Validar consistencia entre tipo_item y las referencias
            if ($movimiento->tipo_item === self::TIPO_MEDICAMENTO && !$tieneMedicamento) {
                throw new \Exception('Si tipo_item es MEDICAMENTO, debe especificar id_medicamento.');
            }

            if ($movimiento->tipo_item === self::TIPO_MATERIAL && !$tieneMaterial) {
                throw new \Exception('Si tipo_item es MATERIAL, debe especificar id_material.');
            }
        });
    }
}