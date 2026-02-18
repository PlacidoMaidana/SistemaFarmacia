<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialesEnfermeria extends Model
{
    use HasFactory;

    protected $table = 'materiales_enfermeria';
    protected $primaryKey = 'id_material';

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'unidad_medida',
        'stock_actual',
        'stock_minimo',
        'ubicacion_fisica'
    ];

    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
    ];

    /**
     * Relación con dispensaciones (suministros de enfermería)
     */
    public function dispensaciones()
    {
        return $this->hasMany(Dispensacion::class, 'id_material', 'id_material');
    }

    /**
     * Suministros realizados de este material
     */
    public function suministros()
    {
        return $this->hasMany(Dispensacion::class, 'id_material', 'id_material')
                    ->where('tipo_origen', 'suministro');
    }
}
