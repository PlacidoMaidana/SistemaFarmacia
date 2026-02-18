<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    protected $table = 'medicos';
    protected $primaryKey = 'id_medico';
    public $timestamps = false; // si no usás created_at / updated_at

    protected $fillable = [
        'NombreYApellido',
        'Especialidad',
        'matricula',
        'Oficial',
        'Grado',
        'Escalafon'
    ];

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'id_medico', 'id_medico');
    }

}
