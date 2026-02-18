<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etiqueta extends Model
{
    use HasFactory;


    public $timestamps = false;
    protected $table = 'etiquetas';
    protected $primaryKey = 'id';
    protected $fillable = [
    'nombre',
    'descripcion',
    ];

 

}
