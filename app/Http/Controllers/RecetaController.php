<?php

// app/Http/Controllers/RecetaController.php
namespace App\Http\Controllers;
use App\Models\Receta;
use App\Models\Dispensacion;
use App\Models\Medicamento;
use App\Models\Interno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class RecetaController extends Controller
{
    public function dispensaciones($id_receta)
    {
        $receta = Receta::with(['interno', 'medico', 'dispensaciones.medicamento'])->findOrFail($id_receta);
        return view('recetas.dispensaciones', compact('receta'));
    }
    public function createDispensacion($id_receta)
    {
        $receta = Receta::findOrFail($id_receta);
        $medicamentos = Medicamento::where('activo', 1)
            ->orderBy('nombre')
            ->get(['id_medicamento', 'nombre', 'nombre_comercial', 'concentracion']);
        return view('recetas.dispensacion_create', compact('receta', 'medicamentos'));
    }
    public function storeDispensacion(Request $request, $id_receta)
    {
        $request->validate([
            'id_medicamento' => 'required|exists:medicamentos,id_medicamento',
            'cantidad'       => 'required|numeric|min:0.01',
            'unidad_medida'  => 'nullable|string|max:30',
            'fecha'          => 'required|date',
            'observaciones'  => 'nullable|string',
        ]);
        $receta = Receta::findOrFail($id_receta);
        Dispensacion::create([
            'tipo_origen'     => 'receta',
            'id_origen'       => $receta->id_receta,
            'id_interno'      => $receta->id_interno,
            'id_medicamento'  => $request->id_medicamento,
            'cantidad'        => $request->cantidad,
            'unidad_medida'   => $request->unidad_medida,
            'fecha'           => $request->fecha,
            'hora'            => now()->format('H:i:s'),
            'id_usuario'      => Auth::id(),
            'observaciones'   => $request->observaciones,
            'es_psicotropico' => Medicamento::find($request->id_medicamento)->es_psicotropico ?? 0,
        ]);
        return redirect()->route('recetas.dispensaciones.index', $receta->id_receta)
            ->with('success', 'Dispensación registrada correctamente.');
    }
    public function destroyDispensacion($id_dispensacion)
    {
        $dispensacion = Dispensacion::where('tipo_origen', 'receta')
            ->findOrFail($id_dispensacion);
        $id_receta = $dispensacion->id_origen;
        $dispensacion->delete();
        return redirect()->route('recetas.dispensaciones.index', $id_receta)
            ->with('success', 'Dispensación eliminada.');
    }
}
