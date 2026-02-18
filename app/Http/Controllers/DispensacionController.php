<?php

namespace App\Http\Controllers;

use TCG\Voyager\Http\Controllers\VoyagerBaseController;
use Illuminate\Http\Request;
use App\Models\Dispensacion;
use App\Models\Receta;
use App\Models\TratamientoCronico;
use Illuminate\Support\Facades\Auth;

class DispensacionController extends VoyagerBaseController
{
    // Método porOrigen (corrige el error - lista dispensaciones por origen)
    public function porOrigen(Request $request)
    {
        $request->validate([
            'tipo_origen' => 'required|in:receta,tratamiento_cronicos,suministros_enfermeria',
            'id_origen' => 'required|integer',
        ]);

        $dispensaciones = Dispensacion::where('tipo_origen', $request->tipo_origen)
            ->where('id_origen', $request->id_origen)
            ->with(['medicamento', 'material', 'usuario'])
            ->orderBy('fecha', 'desc')
            ->get();

        // Definir título dinámico según el tipo de origen
        switch ($request->tipo_origen) {
            case 'receta':
                $tituloOrigen = 'Receta N° ' . $request->id_origen;
                break;
            case 'tratamiento_cronicos':
                $tituloOrigen = 'Tratamiento Crónico ID ' . $request->id_origen;
                break;
            case 'suministros_enfermeria':
                $tituloOrigen = 'Suministro de Enfermería ID ' . $request->id_origen;
                break;
            default:
                $tituloOrigen = 'Origen desconocido';
                break;
        }

        // Opcional: obtener más info del origen para mostrar en la vista
        $origen = null;
        if ($request->tipo_origen === 'receta') {
            $origen = Receta::with('interno', 'medico')->find($request->id_origen);
        } // puedes agregar elseif para otros tipos

        $tipoOrigen = $request->tipo_origen;
        $idOrigen = $request->id_origen;

        return view('dispensaciones.por-origen', compact(
            'dispensaciones',
            'tituloOrigen',
            'tipoOrigen',
            'idOrigen',
            'origen'
        ));
    }

    // Validación al crear dispensación
    public function store(Request $request)
    {
        $request->validate([
            'tipo_origen' => 'required|in:receta,tratamiento_cronicos,suministros_enfermeria',
            'id_origen' => 'required|integer',
            'id_interno' => 'required|exists:internos,id_interno',
            'id_medicamento' => 'nullable|exists:medicamentos,id_medicamento',
            'id_material' => 'nullable|exists:materiales_enfermeria,id_material',
            'cantidad' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
        ]);

        $dispensacion = Dispensacion::create($request->all() + ['id_usuario' => Auth::id()]);

        $this->validarIntegridad($request->tipo_origen, $request->id_origen);

        return redirect()->route('voyager.dispensaciones.index')
                         ->with('success', 'Dispensación creada');
    }

    // Validación al actualizar
    public function update(Request $request, $id)
    {
        $dispensacion = Dispensacion::findOrFail($id);
        $oldTipo = $dispensacion->tipo_origen;
        $oldIdOrigen = $dispensacion->id_origen;

        $dispensacion->update($request->all());

        $this->validarIntegridad($oldTipo, $oldIdOrigen);

        return redirect()->route('voyager.dispensaciones.index')
                         ->with('success', 'Dispensación actualizada');
    }

    // Validación al eliminar
    public function destroy(Request $request, $id)
    {
        $dispensacion = Dispensacion::findOrFail($id);
        $tipo = $dispensacion->tipo_origen;
        $idOrigen = $dispensacion->id_origen;

        $dispensacion->delete();

        $this->validarIntegridad($tipo, $idOrigen);

        return redirect()->route('voyager.dispensaciones.index')
                         ->with('success', 'Dispensación eliminada');
    }

    // Método privado para validar integridad
    protected function validarIntegridad($tipo, $idOrigen)
    {
        $count = Dispensacion::where('tipo_origen', $tipo)
                             ->where('id_origen', $idOrigen)
                             ->count();

        if ($count === 0) {
            $origen = null;
            if ($tipo === 'receta') {
                $origen = Receta::find($idOrigen);
            } elseif ($tipo === 'tratamiento_cronicos') {
                $origen = TratamientoCronico::find($idOrigen);
            }
            // Nota: suministros_enfermeria pendiente de implementar

            if ($origen) $origen->delete();
        }
    }
}