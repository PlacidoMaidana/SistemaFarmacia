<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionCentral extends Model
{
    use HasFactory;

    protected $table = 'recepciones_central';
    protected $primaryKey = 'id_recepcion';

    protected $fillable = [
        'nro_remito',
        'fecha_recepcion',
        'estado',
        'unidad_id',
        'id_usuario',
        'observaciones'
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
    ];

    // Constantes para estados
    const ESTADO_BORRADOR = 'BORRADOR';
    const ESTADO_CONFIRMADA = 'CONFIRMADA';
    const ESTADO_ANULADA = 'ANULADA';

    /**
     * Relación con los items de la recepción
     */
    public function items(): HasMany
    {
        return $this->hasMany(RecepcionCentralItem::class, 'id_recepcion', 'id_recepcion');
    }

    /**
     * Relación con el usuario que registró la recepción
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para recepciones confirmadas
     */
    public function scopeConfirmadas($query)
    {
        return $query->where('estado', self::ESTADO_CONFIRMADA);
    }

    /**
     * Scope para recepciones en borrador
     */
    public function scopeBorradores($query)
    {
        return $query->where('estado', self::ESTADO_BORRADOR);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopePorFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_recepcion', [$fechaInicio, $fechaFin]);
    }

    /**
     * Verificar si la recepción puede ser editada
     */
    public function puedeSerEditada(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR;
    }

    /**
     * Verificar si la recepción puede ser confirmada
     */
    public function puedeSerConfirmada(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR && $this->items()->count() > 0;
    }

    /**
     * Verificar si la recepción puede ser anulada
     */
    public function puedeSerAnulada(): bool
    {
        return $this->estado === self::ESTADO_CONFIRMADA;
    }

    /**
     * Obtener el total de items diferentes en la recepción
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    /**
     * Obtener la cantidad total recibida (suma de todos los items)
     */
    public function getCantidadTotalAttribute()
    {
        return $this->items()->sum('cantidad');
    }

    /**
     * Obtener descripción del estado para mostrar en UI
     */
    public function getDescripcionEstadoAttribute()
    {
        $descripciones = [
            self::ESTADO_BORRADOR => 'En preparación',
            self::ESTADO_CONFIRMADA => 'Confirmada y aplicada al stock',
            self::ESTADO_ANULADA => 'Anulada'
        ];

        return $descripciones[$this->estado] ?? 'Estado desconocido';
    }

    /**
     * Validaciones al guardar
     */
    public static function boot()
    {
        parent::boot();

        // Establecer estado BORRADOR por defecto al crear
        static::creating(function ($recepcion) {
            if (empty($recepcion->estado)) {
                $recepcion->estado = self::ESTADO_BORRADOR;
            }
        });

        static::saving(function ($recepcion) {
            // Nota: Removemos la validación de cambio de estado aquí porque
            // el RecepcionService debe poder cambiar el estado cuando aplica stock
            // La validación se hace a nivel de servicio
        });

        // Al eliminar una recepción, eliminar sus items
        static::deleting(function ($recepcion) {
            if ($recepcion->estado !== self::ESTADO_BORRADOR) {
                throw new \Exception('Solo se pueden eliminar recepciones en estado BORRADOR.');
            }
            
            $recepcion->items()->delete();
        });
    }
}