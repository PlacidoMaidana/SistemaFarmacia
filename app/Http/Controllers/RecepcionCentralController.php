<?php

namespace App\Http\Controllers;

use App\Models\RecepcionCentral;
use App\Models\RecepcionCentralItem;
use App\Models\Medicamento;
use App\Models\MaterialesEnfermeria;
use App\Services\RecepcionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RecepcionCentralController extends Controller
{
    private RecepcionService $recepcionService;

    public function __construct(RecepcionService $recepcionService)
    {
        $this->recepcionService = $recepcionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {        
        $filtros = $request->only(['estado', 'fecha_desde', 'fecha_hasta', 'nro_remito']);
        
        $recepciones = $this->recepcionService->buscarRecepciones($filtros)
            ->paginate(20);

        // Estados para el filtro
        $estados = [
            RecepcionCentral::ESTADO_BORRADOR => 'Borrador',
            RecepcionCentral::ESTADO_CONFIRMADA => 'Confirmada',
            RecepcionCentral::ESTADO_ANULADA => 'Anulada'
        ];

        return view('recepciones.index', compact('recepciones', 'estados', 'filtros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('recepciones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nro_remito' => 'required|string|max:255|unique:recepciones_central,nro_remito',
            'fecha_recepcion' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            $recepcion = RecepcionCentral::create([
                'nro_remito' => $request->nro_remito,
                'fecha_recepcion' => $request->fecha_recepcion,
                'estado' => RecepcionCentral::ESTADO_BORRADOR,
                'id_usuario' => Auth::id(),
                'observaciones' => $request->observaciones,
            ]);

            return redirect()->route('recepciones.edit', $recepcion->id_recepcion)
                           ->with('success', 'Recepción creada exitosamente. Ahora puede agregar items.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear la recepción: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RecepcionCentral $recepcion)
    {
        $recepcion->load(['items.medicamento', 'items.material', 'usuario']);
        $resumen = $this->recepcionService->getResumenRecepcion($recepcion);

        return view('recepciones.show', compact('recepcion', 'resumen'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RecepcionCentral $recepcion)
    {
        if (!$recepcion->puedeSerEditada()) {
            return redirect()->route('recepciones.show', $recepcion->id_recepcion)
                           ->with('warning', 'Esta recepción no puede ser editada porque ya fue confirmada o anulada.');
        }

        $recepcion->load(['items.medicamento', 'items.material']);
        
        // Obtener medicamentos y materiales para los selects
        $medicamentos = Medicamento::where('activo', true)
                                  ->orderBy('nombre')
                                  ->get(['id_medicamento', 'nombre', 'stock_actual']);
        
        $materiales = MaterialesEnfermeria::orderBy('nombre')
                                         ->get(['id_material', 'nombre', 'stock_actual']);

        return view('recepciones.edit', compact('recepcion', 'medicamentos', 'materiales'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecepcionCentral $recepcion)
    {
        if (!$recepcion->puedeSerEditada()) {
            return redirect()->route('recepciones.show', $recepcion->id_recepcion)
                           ->with('error', 'Esta recepción no puede ser modificada.');
        }

        $request->validate([
            'nro_remito' => 'required|string|max:255|unique:recepciones_central,nro_remito,' . $recepcion->id_recepcion . ',id_recepcion',
            'fecha_recepcion' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        try {
            $recepcion->update($request->only(['nro_remito', 'fecha_recepcion', 'observaciones']));

            return back()->with('success', 'Recepción actualizada exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la recepción: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecepcionCentral $recepcion)
    {
        if (!$recepcion->puedeSerEditada()) {
            return redirect()->route('recepciones.index')
                           ->with('error', 'Solo se pueden eliminar recepciones en estado borrador.');
        }

        try {
            $nroRemito = $recepcion->nro_remito;
            $recepcion->delete();

            return redirect()->route('recepciones.index')
                           ->with('success', "Recepción {$nroRemito} eliminada exitosamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la recepción: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar una recepción (aplicar stock)
     */
    public function confirmar(RecepcionCentral $recepcion)
    {
        try {
            $resultado = $this->recepcionService->confirmarRecepcion($recepcion);

            return redirect()->route('recepciones.show', $recepcion->id_recepcion)
                           ->with('success', $resultado['mensaje']);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al confirmar la recepción: ' . $e->getMessage());
        }
    }

    /**
     * Anular una recepción confirmada
     */
    public function anular(Request $request, RecepcionCentral $recepcion)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        try {
            $resultado = $this->recepcionService->anularRecepcion($recepcion, $request->motivo);

            return redirect()->route('recepciones.show', $recepcion->id_recepcion)
                           ->with('success', $resultado['mensaje']);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al anular la recepción: ' . $e->getMessage());
        }
    }

    /**
     * Agregar item a una recepción
     */
    public function agregarItem(Request $request, RecepcionCentral $recepcion)
    {
        if (!$recepcion->puedeSerEditada()) {
            return response()->json(['error' => 'Esta recepción no puede ser modificada.'], 400);
        }

        $request->validate([
            'tipo_item' => 'required|in:MEDICAMENTO,MATERIAL',
            'id_medicamento' => 'required_if:tipo_item,MEDICAMENTO|nullable|exists:medicamentos,id_medicamento',
            'id_material' => 'required_if:tipo_item,MATERIAL|nullable|exists:materiales_enfermeria,id_material',
            'cantidad' => 'required|numeric|min:0.01',
            'nro_lote' => 'nullable|string|max:255',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
        ], [
            'tipo_item.required' => 'Debe seleccionar el tipo de item.',
            'id_medicamento.required_if' => 'Debe seleccionar un medicamento.',
            'id_medicamento.exists' => 'El medicamento seleccionado no existe.',
            'id_material.required_if' => 'Debe seleccionar un material.',
            'id_material.exists' => 'El material seleccionado no existe.',
            'cantidad.required' => 'La cantidad es requerida.',
            'cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy.',
        ]);

        try {
            $item = RecepcionCentralItem::create([
                'id_recepcion' => $recepcion->id_recepcion,
                'tipo_item' => $request->tipo_item,
                'id_medicamento' => $request->tipo_item === 'MEDICAMENTO' ? $request->id_medicamento : null,
                'id_material' => $request->tipo_item === 'MATERIAL' ? $request->id_material : null,
                'cantidad' => $request->cantidad,
                'nro_lote' => $request->nro_lote,
                'fecha_vencimiento' => $request->fecha_vencimiento,
            ]);

            $item->load(['medicamento', 'material']);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'item' => $item,
                    'html' => view('recepciones.partials.item_row', compact('item'))->render()
                ]);
            }

            return back()->with('success', 'Item agregado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Errores de validación: ' . implode(' ', $e->validator->errors()->all())
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error al agregar item: ' . $e->getMessage(), [
                'recepcion_id' => $recepcion->id_recepcion,
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Error al agregar el item: ' . $e->getMessage()], 500);
            }
            
            return back()->withErrors(['error' => 'Error al agregar el item: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar item de una recepción
     */
    public function eliminarItem(RecepcionCentral $recepcion, RecepcionCentralItem $item)
    {
        if (!$recepcion->puedeSerEditada()) {
            return response()->json(['error' => 'Esta recepción no puede ser modificada.'], 400);
        }

        if ($item->id_recepcion !== $recepcion->id_recepcion) {
            return response()->json(['error' => 'Item no pertenece a esta recepción.'], 400);
        }

        try {
            $item->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Buscar medicamentos/materiales para el autocomplete
     */
    public function buscarItems(Request $request)
    {
        $tipo = $request->get('tipo');
        $query = $request->get('q', '');

        $items = [];

        if ($tipo === 'MEDICAMENTO') {
            $items = Medicamento::where('activo', true)
                               ->where('nombre', 'LIKE', "%{$query}%")
                               ->orderBy('nombre')
                               ->limit(10)
                               ->get(['id_medicamento as id', 'nombre', 'stock_actual']);
        } elseif ($tipo === 'MATERIAL') {
            $items = MaterialesEnfermeria::where('nombre', 'LIKE', "%{$query}%")
                                       ->orderBy('nombre')
                                       ->limit(10)
                                       ->get(['id_material as id', 'nombre', 'stock_actual']);
        }

        return response()->json($items);
    }
}