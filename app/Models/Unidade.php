<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    use HasFactory;

    public $timestamps = false;
    // Nombre de la tabla explícito
    protected $table = 'unidades';

    // Clave primaria
    protected $primaryKey = 'id';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'nombre',
        'detalles',
    ];

    // Relaciones: una unidade contiene muchos pabellones
    public function pabellones()
    {
        return $this->hasMany(Pabellon::class, 'UnidadAlojamiento', 'id');
    }
}

