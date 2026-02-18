<?php

namespace App\Http\Controllers;

use App\Models\Dispensacion;
use App\Models\Receta;
use App\Models\TratamientoCronico;
use App\Models\Medicamento;
use App\Models\MaterialesEnfermeria;
use App\Models\Interno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispensacionController extends Controller
{
    /**
     * Mostrar dispensaciones filtradas por origen
     */
    public function porOrigen(Request $request)
    {
        $tipoOrigen = $request->get('tipo_origen');
        $idOrigen = $request->get('id_origen');

        // Validar parámetros
        if (!$tipoOrigen || !$idOrigen) {
            return redirect()->back()->with('error', 'Parámetros inválidos');
        }

        // Obtener información del origen
        $origen = null;
        $tituloOrigen = '';
        
        switch ($tipoOrigen) {
            case 'receta':
                $origen = Receta::with(['interno', 'medico'])->find($idOrigen);
                if ($origen) {
                    $tituloOrigen = "Receta #{$origen->id_receta} - {$origen->interno->nombre_completo}";
                }
                break;
            case 'tratamiento':
                $origen = TratamientoCronico::with(['interno', 'medicamento'])->find($idOrigen);
                if ($origen) {
                    $tituloOrigen = "Tratamiento Crónico #{$origen->id_tratamiento} - {$origen->interno->nombre_completo}";
                }
                break;
            case 'suministro':
                $tituloOrigen = "Suministro #{$idOrigen}";
                break;
        }

        // Si no se encuentra el origen
        if (!$origen && $tipoOrigen !== 'suministro') {
            return redirect()->back()->with('error', 'Origen no encontrado');
        }

        // Obtener dispensaciones
        $dispensaciones = Dispensacion::with(['medicamento', 'material', 'interno', 'usuario'])
            ->where('tipo_origen', $tipoOrigen)
            ->where('id_origen', $idOrigen)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        return view('dispensaciones.por-origen', compact('dispensaciones', 'origen', 'tipoOrigen', 'idOrigen', 'tituloOrigen'));
    }

    /**
     * Mostrar formulario para crear nueva dispensación
     */
    public function create(Request $request)
    {
        $tipoOrigen = $request->get('tipo_origen');
        $idOrigen = $request->get('id_origen');

        // Obtener información del origen
        $origen = null;
        switch ($tipoOrigen) {
            case 'receta':
                $origen = Receta::with(['interno'])->find($idOrigen);
                break;
            case 'tratamiento':
                $origen = TratamientoCronico::with(['interno'])->find($idOrigen);
                break;
        }

        if (!$origen) {
            return redirect()->back()->with('error', 'Origen no encontrado');
        }

        $medicamentos = Medicamento::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $materiales = MaterialesEnfermeria::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view('dispensaciones.create', compact('origen', 'tipoOrigen', 'idOrigen', 'medicamentos', 'materiales'));
    }

    /**
     * Guardar nueva dispensación
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_origen' => 'required|in:receta,tratamiento,suministro',
            'id_origen' => 'required|integer',
            'id_interno' => 'required|exists:internos,id_interno',
            'cantidad' => 'required|numeric|min:0.01',
            'unidad_medida' => 'nullable|string|max:30',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string',
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
            $medicamento = Medicamento::find($request->id_medicamento);
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
            'lote' => $request->lote,
            'fecha_vencimiento' => $request->fecha_vencimiento,
        ]);

        return redirect()->route('dispensaciones.por-origen', [
            'tipo_origen' => $request->tipo_origen,
            'id_origen' => $request->id_origen
        ])->with('success', 'Dispensación registrada correctamente');
    }

    /**
     * Mostrar listado general de dispensaciones
     */
    public function index(Request $request)
    {
        $query = Dispensacion::with(['medicamento', 'material', 'interno', 'usuario']);

        // Filtros
        if ($request->filled('tipo_origen')) {
            $query->where('tipo_origen', $request->tipo_origen);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('id_interno')) {
            $query->where('id_interno', $request->id_interno);
        }

        $dispensaciones = $query->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(50);

        $internos = Interno::orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();

        return view('dispensaciones.index', compact('dispensaciones', 'internos'));
    }

    /**
     * Eliminar una dispensación
     */
    public function destroy($id)
    {
        $dispensacion = Dispensacion::findOrFail($id);

        // Contar cuántas dispensaciones tiene el mismo origen
        $count = Dispensacion::where('tipo_origen', $dispensacion->tipo_origen)
            ->where('id_origen', $dispensacion->id_origen)
            ->count();

        // No permitir eliminar si es la única
        if ($count <= 1) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar la única dispensación. Debe haber al menos una dispensación por origen.');
        }

        $tipoOrigen = $dispensacion->tipo_origen;
        $idOrigen = $dispensacion->id_origen;

        $dispensacion->delete();

        return redirect()->route('dispensaciones.por-origen', [
            'tipo_origen' => $tipoOrigen,
            'id_origen' => $idOrigen
        ])->with('success', 'Dispensación eliminada correctamente');
    }
}
