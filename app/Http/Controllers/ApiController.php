<?php
namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Interno;
use App\Models\Medicamento;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function medicos(Request $request)
    {
        return Medico::select('id_medico', 'nombre_y_apellido', 'especialidad', 'matricula')
            ->when($request->search, function($q, $search) {
                $q->where('nombre_y_apellido', 'like', "%$search%")
                  ->orWhere('especialidad', 'like', "%$search%");
            })
            ->paginate(10);
    }

    public function internos(Request $request)
    {
        return Interno::select('id_interno', 'nombre_y_apellido', 'lpu')
            ->with('pabellon:id,pabellon')
            ->when($request->search, function($q, $search) {
                $q->where('nombre_y_apellido', 'like', "%$search%")
                  ->orWhere('lpu', 'like', "%$search%");
            })
            ->paginate(10);
    }

    public function medicamentos(Request $request)
    {
        return Medicamento::select('id_medicamento', 'nombre', 'concentracion', 'nombre_comercial', 'es_psicotropico')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();
    }
}