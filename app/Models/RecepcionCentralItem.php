<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionCentralItem extends Model
{
    use HasFactory;

    protected $table = 'recepciones_central_items';
    protected $primaryKey = 'id_item';

    protected $fillable = [
        'id_recepcion',
        'tipo_item',
        'id_medicamento',
        'id_material',
        'cantidad',
        'nro_lote',
        'fecha_vencimiento'
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    // Constantes para tipos de item (mismo que MovimientoStock)
    const TIPO_MEDICAMENTO = 'MEDICAMENTO';
    const TIPO_MATERIAL = 'MATERIAL';

    /**
     * Relación con la cabecera de recepción
     */
    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(RecepcionCentral::class, 'id_recepcion', 'id_recepcion');
    }

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
     * Obtener el item al que se refiere (medicamento o material)
     */
    public function getItemAttribute()
    {
        if ($this->tipo_item === self::TIPO_MEDICAMENTO && $this->medicamento) {
            return $this->medicamento;
        }
        
        if ($this->tipo_item === self::TIPO_MATERIAL && $this->material) {
            return $this->material;
        }
        
        return null;
    }

    /**
     * Obtener el nombre del item
     */
    public function getNombreItemAttribute()
    {
        $item = $this->item;
        return $item ? $item->nombre : 'Item no encontrado';
    }

    /**
     * Obtener descripción completa del item para mostrar en UI
     */
    public function getDescripcionCompletaAttribute()
    {
        $nombre = $this->nombre_item;
        $cantidad = $this->cantidad;
        $lote = $this->nro_lote ? " (Lote: {$this->nro_lote})" : '';
        $vencimiento = $this->fecha_vencimiento ? " - Vence: {$this->fecha_vencimiento->format('d/m/Y')}" : '';
        
        return "{$nombre} - {$cantidad} unidades{$lote}{$vencimiento}";
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
     * Scope para items próximos a vencer
     */
    public function scopeProximosAVencer($query, $diasAnticipacion = 30)
    {
        $fechaLimite = now()->addDays($diasAnticipacion);
        return $query->where('fecha_vencimiento', '<=', $fechaLimite)
                     ->where('fecha_vencimiento', '>=', now());
    }

    /**
     * Preparar datos para aplicar al stock usando StockService
     */
    public function prepararDatosMovimiento()
    {
        return [
            'tipo_item' => $this->tipo_item,
            'id_medicamento' => $this->id_medicamento,
            'id_material' => $this->id_material,
            'tipo_movimiento' => MovimientoStock::INGRESO_CENTRAL,
            'cantidad' => $this->cantidad,
            'nro_lote' => $this->nro_lote,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'origen_tipo' => MovimientoStock::ORIGEN_RECEPCION_CENTRAL,
            'origen_id' => $this->id_recepcion,
            'observaciones' => "Ingreso desde Farmacia Central - Remito: {$this->recepcion->nro_remito}"
        ];
    }

    /**
     * Validaciones al guardar
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Validar que tenga medicamento O material (XOR)
            $tieneMedicamento = !empty($item->id_medicamento);
            $tieneMaterial = !empty($item->id_material);
            
            if (!$tieneMedicamento && !$tieneMaterial) {
                throw new \Exception('El item debe referenciar un medicamento o un material.');
            }
            
            if ($tieneMedicamento && $tieneMaterial) {
                throw new \Exception('El item no puede referenciar medicamento y material simultáneamente.');
            }

            // Validar consistencia entre tipo_item y las referencias
            if ($item->tipo_item === self::TIPO_MEDICAMENTO && !$tieneMedicamento) {
                throw new \Exception('Si tipo_item es MEDICAMENTO, debe especificar id_medicamento.');
            }

            if ($item->tipo_item === self::TIPO_MATERIAL && !$tieneMaterial) {
                throw new \Exception('Si tipo_item es MATERIAL, debe especificar id_material.');
            }

            // Validar cantidad positiva
            if ($item->cantidad <= 0) {
                throw new \Exception('La cantidad debe ser mayor a 0.');
            }

            // Validar que la recepción esté en estado editable
            if ($item->recepcion && !$item->recepcion->puedeSerEditada()) {
                throw new \Exception('No se pueden agregar items a una recepción que no está en estado BORRADOR.');
            }
        });
    }
}