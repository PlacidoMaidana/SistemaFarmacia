<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pabellon extends Model
{
    use HasFactory;

     public $timestamps = false;

    // Nombre de la tabla explícito (si tu tabla se llama 'pabellon')
    protected $table = 'pabellon';

    // Clave primaria
    protected $primaryKey = 'id';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'pabellon',
        'UnidadAlojamiento',
    ];

    // 🔹 Relación: un pabellón pertenece a una unidade
    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'UnidadAlojamiento', 'id');
    }

    // 🔹 Relación: un pabellón tiene muchos internos
    public function internos()
    {
        return $this->hasMany(Interno::class, 'id_pabellon', 'id');
    }

    // 🔹 Relación: un pabellón puede tener muchos cambios de alojamiento
    public function cambiosAlojamiento()
    {
        return $this->hasMany(CambioAlojamiento::class, 'pabellon_destino', 'id');
    }

    // 🔹 Relación: un pabellón puede ser origen de cambios de alojamiento
    public function cambiosAlojamientoOrigen()
    {
        return $this->hasMany(CambioAlojamiento::class, 'pabellon_origen', 'id');
    }
}