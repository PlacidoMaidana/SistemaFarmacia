<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interno extends Model
{
    use HasFactory;


    protected $table = 'internos';
    protected $primaryKey = 'id_interno';
    protected $fillable = [
        'id_pabellon',
        'lpu',
        'nombre_y_apellido',
        'foto',
        'detalles',
    ];

    public function pabellon()
    {
        return $this->belongsTo(Pabellon::class, 'id_pabellon', 'id');
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'id_interno', 'id_interno');
    }
}
