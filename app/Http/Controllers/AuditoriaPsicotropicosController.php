<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditoriaPsicotropicos;
use App\Models\Medicamento;
use App\Models\Interno;
use App\Models\User;

class AuditoriaPsicotropicosController extends Controller
{
    /**
     * Mostrar el índice de auditoría de psicotrópicos
     */
    public function index(Request $request)
    {
        $query = AuditoriaPsicotropicos::with([
            'medicamento',
            'interno', 
            'usuarioDispensador',
            'usuarioEliminador'
        ]);

        // Filtros
        if ($request->filled('fecha_inicio')) {
            $query->where('fecha_eliminacion', '>=', $request->fecha_inicio . ' 00:00:00');
        }

        if ($request->filled('fecha_fin')) {
            $query->where('fecha_eliminacion', '<=', $request->fecha_fin . ' 23:59:59');
        }

        if ($request->filled('id_medicamento')) {
            $query->where('id_medicamento', $request->id_medicamento);
        }

        if ($request->filled('id_interno')) {
            $query->where('id_interno', $request->id_interno);
        }

        if ($request->filled('tipo_origen')) {
            $query->where('tipo_origen', $request->tipo_origen);
        }

        if ($request->filled('tipo_accion')) {
            $query->where('tipo_accion', $request->tipo_accion);
        }

        $registros = $query->orderBy('fecha_eliminacion', 'desc')->paginate(50);

        // Datos para filtros
        $medicamentos = Medicamento::where('es_psicotropico', 1)
            ->orderBy('nombre')
            ->get();

        $internos = Interno::orderBy('nombre_y_apellido')->get();

        return view('auditoria-psicotropicos.index', compact(
            'registros',
            'medicamentos', 
            'internos'
        ));
    }

    /**
     * Mostrar detalles de un registro específico
     */
    public function show($id)
    {
        $registro = AuditoriaPsicotropicos::with([
            'medicamento',
            'interno',
            'usuarioDispensador', 
            'usuarioEliminador'
        ])->findOrFail($id);

        return view('auditoria-psicotropicos.show', compact('registro'));
    }

    /**
     * Exportar reporte a Excel/CSV
     */
    public function exportar(Request $request)
    {
        $query = AuditoriaPsicotropicos::with([
            'medicamento',
            'interno',
            'usuarioDispensador',
            'usuarioEliminador'
        ]);

        // Aplicar los mismos filtros que en index
        if ($request->filled('fecha_inicio')) {
            $query->where('fecha_eliminacion', '>=', $request->fecha_inicio . ' 00:00:00');
        }

        if ($request->filled('fecha_fin')) {
            $query->where('fecha_eliminacion', '<=', $request->fecha_fin . ' 23:59:59');
        }

        $registros = $query->orderBy('fecha_eliminacion', 'desc')->get();

        // Generar CSV
        $csvHeader = [
            'ID Auditoría',
            'Fecha Eliminación',
            'Interno',
            'Medicamento',
            'Cantidad',
            'Fecha Dispensación',
            'Usuario Dispensó',
            'Usuario Eliminó',
            'Motivo',
            'Tipo Origen',
            'ID Origen',
            'Observaciones'
        ];

        $filename = 'auditoria_psicotropicos_' . date('Y-m-d_H-i-s') . '.csv';
        
        $callback = function() use ($registros, $csvHeader) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $csvHeader, ';');
            
            foreach ($registros as $registro) {
                fputcsv($file, [
                    $registro->id_auditoria,
                    $registro->fecha_eliminacion->format('d/m/Y H:i:s'),
                    $registro->interno->nombre_y_apellido ?? 'N/A',
                    $registro->medicamento->nombre ?? 'N/A',
                    $registro->cantidad,
                    $registro->fecha_dispensacion->format('d/m/Y'),
                    $registro->usuarioDispensador->name ?? 'N/A',
                    $registro->usuarioEliminador->name ?? 'N/A',
                    $registro->motivo_eliminacion,
                    $registro->tipo_origen,
                    $registro->id_origen,
                    $registro->observaciones_auditoria,
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Dashboard con estadísticas
     */
    public function dashboard()
    {
        $estadisticas = [
            'total_eliminaciones' => AuditoriaPsicotropicos::count(),
            'eliminaciones_mes_actual' => AuditoriaPsicotropicos::whereMonth('fecha_eliminacion', now()->month)->count(),
            'medicamentos_mas_eliminados' => AuditoriaPsicotropicos::select('id_medicamento', \DB::raw('count(*) as total'))
                ->with('medicamento')
                ->groupBy('id_medicamento')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get(),
            'usuarios_mas_eliminaciones' => AuditoriaPsicotropicos::select('id_usuario_elimino', \DB::raw('count(*) as total'))
                ->with('usuarioEliminador')
                ->groupBy('id_usuario_elimino')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get(),
        ];

        return view('auditoria-psicotropicos.dashboard', compact('estadisticas'));
    }
}