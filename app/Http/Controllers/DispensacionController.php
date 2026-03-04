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

    /**
     * Mostrar formulario para crear nueva dispensación
     */
    public function create(Request $request)
    {
        $tipoOrigen = $request->get('tipo_origen');
        $idOrigen = $request->get('id_origen');

        // Validar parámetros
        if (!$tipoOrigen || !$idOrigen) {
            return redirect()->back()->with('error', 'Parámetros inválidos');
        }

        // Obtener información del origen
        $origen = null;
        switch ($tipoOrigen) {
            case 'receta':
                $origen = Receta::with(['interno', 'medico'])->find($idOrigen);
                break;
            case 'tratamiento_cronicos':
                $origen = TratamientoCronico::with(['interno'])->find($idOrigen);
                break;
        }

        if (!$origen) {
            return redirect()->back()->with('error', 'Origen no encontrado');
        }

        // Obtener medicamentos y materiales
        $medicamentos = \App\Models\Medicamento::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $materiales = \App\Models\MaterialesEnfermeria::orderBy('nombre')
            ->get();

        return view('dispensaciones.create', compact('origen', 'tipoOrigen', 'idOrigen', 'medicamentos', 'materiales'));
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
            'unidad_medida' => 'nullable|string|max:30',
            'observaciones' => 'nullable|string',
            'nro_lote' => 'nullable|string|max:50',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        // Validar que tenga medicamento O material (no ambos, no ninguno)
        $tieneMedicamento = !empty($request->id_medicamento);
        $tieneMaterial = !empty($request->id_material);

        if (!$tieneMedicamento && !$tieneMaterial) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Debe seleccionar un medicamento o un material');
        }

        if ($tieneMedicamento && $tieneMaterial) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No puede seleccionar medicamento y material al mismo tiempo');
        }

        // Determinar si es psicotrópico
        $esPsicotropico = 0;
        if ($tieneMedicamento) {
            $medicamento = \App\Models\Medicamento::find($request->id_medicamento);
            $esPsicotropico = $medicamento->es_psicotropico ?? 0;
        }

        // Crear dispensación
        Dispensacion::create([
            'tipo_origen' => $request->tipo_origen,
            'id_origen' => $request->id_origen,
            'id_interno' => $request->id_interno,
            'id_medicamento' => $request->id_medicamento,
            'id_material' => $request->id_material,
            'cantidad' => $request->cantidad,
            'unidad_medida' => $request->unidad_medida,
            'fecha' => $request->fecha,
            'hora' => now()->format('H:i:s'),
            'id_usuario' => Auth::id(),
            'observaciones' => $request->observaciones,
            'es_psicotropico' => $esPsicotropico,
            'nro_lote' => $request->nro_lote,
            'fecha_vencimiento' => $request->fecha_vencimiento,
        ]);

        return redirect()->route('dispensaciones.por-origen', [
            'tipo_origen' => $request->tipo_origen,
            'id_origen' => $request->id_origen
        ])->with('success', 'Dispensación creada correctamente');
    }

    // Validación al actualizar
    public function update(Request $request, $id)
    {
        $dispensacion = Dispensacion::findOrFail($id);
        $oldTipo = $dispensacion->tipo_origen;
        $oldIdOrigen = $dispensacion->id_origen;

        $dispensacion->update($request->all());

        $this->validarIntegridad($oldTipo, $oldIdOrigen);

        return redirect()->route('dispensaciones.por-origen', [
            'tipo_origen' => $dispensacion->tipo_origen,
            'id_origen' => $dispensacion->id_origen
        ])->with('success', 'Dispensación actualizada correctamente');
    }

    // Validación al eliminar
    public function destroy(Request $request, $id)
    {
        $dispensacion = Dispensacion::findOrFail($id);
        $tipo = $dispensacion->tipo_origen;
        $idOrigen = $dispensacion->id_origen;

        // Contar cuántas dispensaciones tiene el mismo origen
        $count = Dispensacion::where('tipo_origen', $tipo)
            ->where('id_origen', $idOrigen)
            ->count();

        // No permitir eliminar si es la única
        if ($count <= 1) {
            return redirect()->route('dispensaciones.por-origen', [
                'tipo_origen' => $tipo,
                'id_origen' => $idOrigen
            ])->with('error', 'No se puede eliminar la única dispensación. Debe haber al menos una dispensación por origen.');
        }

        $dispensacion->delete();

        return redirect()->route('dispensaciones.por-origen', [
            'tipo_origen' => $tipo,
            'id_origen' => $idOrigen
        ])->with('success', 'Dispensación eliminada correctamente');
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