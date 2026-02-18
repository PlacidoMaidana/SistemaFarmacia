<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    use HasFactory;
    //public $timestamps = false;
    protected $table = 'medicamentos';
    protected $primaryKey = 'id_medicamento';

    protected $fillable = [
        'nombre',
        'nombre_comercial',
        'descripcion',
        'codigo_barra',
        'monodroga',
        'concentracion',
        'es_psicotropico',
        'es_estupefaciente',
        'tipo_control_anmat',
        'id_presentacion',
        'laboratorio',
        'nro_registro_anmat',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'unidad_medida_base',
        'ubicacion_fisica',
        'fecha_vencimiento_principal',
        'activo'
    ];

    // Relación con la tabla presentaciones
    public function presentacion()
    {
        return $this->belongsTo(Presentacion::class, 'id_presentacion');
    }

 

}
