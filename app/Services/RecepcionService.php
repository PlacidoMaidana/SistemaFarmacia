<?php

namespace App\Services;

use App\Models\RecepcionCentral;
use App\Models\RecepcionCentralItem;
use App\Models\MovimientoStock;
use App\Models\AuditoriaPsicotropicos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class RecepcionService
{
    private StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Confirmar una recepción: aplicar todos los items al stock
     * 
     * @param RecepcionCentral $recepcion
     * @return array Resultado con movimientos creados
     * @throws Exception
     */
    public function confirmarRecepcion(RecepcionCentral $recepcion): array
    {
        // Validar que se puede confirmar
        if (!$recepcion->puedeSerConfirmada()) {
            throw new Exception(
                'La recepción no puede ser confirmada. ' .
                'Debe estar en estado BORRADOR y tener al menos un item.'
            );
        }

        return DB::transaction(function () use ($recepcion) {
            $movimientos = [];
            $auditoriasCreadas = [];

            // Procesar cada item de la recepción
            foreach ($recepcion->items as $item) {
                // Aplicar movimiento de stock usando StockService
                $movimiento = $this->stockService->applyMovement($item->prepararDatosMovimiento());
                $movimientos[] = $movimiento;

                // Si es medicamento controlado, crear auditoría
                if ($this->esItemControlado($item)) {
                    $auditoria = $this->crearAuditoriaIngreso($item, $movimiento);
                    $auditoriasCreadas[] = $auditoria;
                }
            }

            // Cambiar estado de la recepción
            $recepcion->update(['estado' => RecepcionCentral::ESTADO_CONFIRMADA]);

            return [
                'recepcion' => $recepcion,
                'movimientos' => $movimientos,
                'auditorias' => $auditoriasCreadas,
                'mensaje' => "Recepción {$recepcion->nro_remito} confirmada exitosamente. " .
                           "Se aplicaron " . count($movimientos) . " movimientos al stock."
            ];
        });
    }

    /**
     * Anular una recepción confirmada: reversar todos los movimientos de stock
     * 
     * @param RecepcionCentral $recepcion
     * @param string $motivo
     * @return array
     * @throws Exception
     */
    public function anularRecepcion(RecepcionCentral $recepcion, string $motivo): array
    {
        if (!$recepcion->puedeSerAnulada()) {
            throw new Exception('Solo se pueden anular recepciones confirmadas.');
        }

        return DB::transaction(function () use ($recepcion, $motivo) {
            $movimientosReversa = [];
            $auditoriasReversa = [];

            // Generar movimientos de reversa para cada item
            foreach ($recepcion->items as $item) {
                $datosReversa = [
                    'tipo_item' => $item->tipo_item,
                    'id_medicamento' => $item->id_medicamento,
                    'id_material' => $item->id_material,
                    'tipo_movimiento' => MovimientoStock::AJUSTE, // Ajuste administrativo
                    'cantidad' => $item->cantidad, // Cantidad positiva: será restada porque es ajuste
                    'nro_lote' => $item->nro_lote,
                    'fecha_vencimiento' => $item->fecha_vencimiento,
                    'origen_tipo' => MovimientoStock::ORIGEN_AJUSTE,
                    'origen_id' => $recepcion->id_recepcion,
                    'observaciones' => "ANULACIÓN de recepción {$recepcion->nro_remito}. Motivo: {$motivo}",
                    'permitir_negativo' => true // Permitir stock negativo para la anulación
                ];

                // Para anulación necesitamos restar, así que usamos lógica inversa
                // Modificar StockService temporalmente o crear lógica especial
                $movimiento = $this->aplicarAjusteReversa($datosReversa);
                $movimientosReversa[] = $movimiento;

                // Si es medicamento controlado, crear auditoría de reversa
                if ($this->esItemControlado($item)) {
                    $auditoria = $this->crearAuditoriaReversa($item, $movimiento, $motivo);
                    $auditoriasReversa[] = $auditoria;
                }
            }

            // Cambiar estado de la recepción
            $recepcion->update([
                'estado' => RecepcionCentral::ESTADO_ANULADA,
                'observaciones' => ($recepcion->observaciones ? $recepcion->observaciones . "\n" : '') .
                                 "ANULADA: {$motivo} (Usuario: " . Auth::user()->name . ", Fecha: " . now()->format('d/m/Y H:i') . ")"
            ]);

            return [
                'recepcion' => $recepcion,
                'movimientos_reversa' => $movimientosReversa,
                'auditorias' => $auditoriasReversa,
                'mensaje' => "Recepción {$recepcion->nro_remito} anulada exitosamente. " .
                           "Se revirtieron " . count($movimientosReversa) . " movimientos de stock."
            ];
        });
    }

    /**
     * Verificar si un item es controlado (psicotrópico/estupefaciente)
     */
    private function esItemControlado(RecepcionCentralItem $item): bool
    {
        if ($item->tipo_item === RecepcionCentralItem::TIPO_MEDICAMENTO && $item->medicamento) {
            return $item->medicamento->es_psicotropico || $item->medicamento->es_estupefaciente;
        }
        
        return false; // Los materiales de enfermería generalmente no son controlados
    }

    /**
     * Crear auditoría para ingreso de medicamento controlado
     */
    private function crearAuditoriaIngreso(RecepcionCentralItem $item, MovimientoStock $movimiento)
    {
        // Nota: Adaptar según la estructura de AuditoriaPsicotropicos
        // Por ahora esto es conceptual - necesitaría ajustarse según el modelo real
        
        return AuditoriaPsicotropicos::create([
            'id_dispensacion' => null, // No es dispensación, es recepción
            'tipo_origen' => 'recepcion_central',
            'id_origen' => $item->id_recepcion,
            'id_interno' => null, // No hay interno en recepciones
            'id_medicamento' => $item->id_medicamento,
            'cantidad' => $item->cantidad,
            'fecha_dispensacion' => $item->recepcion->fecha_recepcion,
            'hora_dispensacion' => now()->format('H:i:s'),
            'id_usuario_dispenso' => $item->recepcion->id_usuario,
            'tipo_accion' => 'ingreso',
            'motivo_eliminacion' => 'Ingreso desde Farmacia Central - Remito: ' . $item->recepcion->nro_remito,
            'fecha_eliminacion' => now(),
            'id_usuario_elimino' => Auth::id(),
            'nro_lote' => $item->nro_lote,
            'fecha_vencimiento' => $item->fecha_vencimiento,
        ]);
    }

    /**
     * Crear auditoría para reversa de medicamento controlado
     */
    private function crearAuditoriaReversa(RecepcionCentralItem $item, MovimientoStock $movimiento, string $motivo)
    {
        return AuditoriaPsicotropicos::create([
            'id_dispensacion' => null,
            'tipo_origen' => 'recepcion_central',
            'id_origen' => $item->id_recepcion,
            'id_interno' => null,
            'id_medicamento' => $item->id_medicamento,
            'cantidad' => $item->cantidad,
            'fecha_dispensacion' => $item->recepcion->fecha_recepcion,
            'hora_dispensacion' => now()->format('H:i:s'),
            'id_usuario_dispenso' => $item->recepcion->id_usuario,
            'tipo_accion' => 'reversa_ingreso',
            'motivo_eliminacion' => "Anulación de recepción. Motivo: {$motivo}",
            'fecha_eliminacion' => now(),
            'id_usuario_elimino' => Auth::id(),
            'nro_lote' => $item->nro_lote,
            'fecha_vencimiento' => $item->fecha_vencimiento,
        ]);
    }

    /**
     * Obtener resumen de una recepción para mostrar en UI
     */
    public function getResumenRecepcion(RecepcionCentral $recepcion): array
    {
        $itemsPorTipo = $recepcion->items()
            ->selectRaw('tipo_item, COUNT(*) as cantidad_items, SUM(cantidad) as cantidad_total')
            ->groupBy('tipo_item')
            ->get()
            ->keyBy('tipo_item');

        $medicamentosControlados = 0;
        if ($recepcion->items()->porTipoItem('MEDICAMENTO')->count() > 0) {
            $medicamentosControlados = $recepcion->items()
                ->porTipoItem('MEDICAMENTO')
                ->whereHas('medicamento', function($query) {
                    $query->where('es_psicotropico', true)
                          ->orWhere('es_estupefaciente', true);
                })
                ->count();
        }

        return [
            'recepcion' => $recepcion,
            'total_items' => $recepcion->total_items,
            'cantidad_total' => $recepcion->cantidad_total,
            'medicamentos' => [
                'cantidad_items' => $itemsPorTipo['MEDICAMENTO']->cantidad_items ?? 0,
                'cantidad_total' => $itemsPorTipo['MEDICAMENTO']->cantidad_total ?? 0,
                'controlados' => $medicamentosControlados,
            ],
            'materiales' => [
                'cantidad_items' => $itemsPorTipo['MATERIAL']->cantidad_items ?? 0,
                'cantidad_total' => $itemsPorTipo['MATERIAL']->cantidad_total ?? 0,
            ],
        ];
    }

    /**
     * Aplicar ajuste de reversa (reducir stock) para anulación de recepción
     */
    private function aplicarAjusteReversa(array $datos): MovimientoStock
    {
        // Crear datos para el StockService simulando un egreso
        $datosEgreso = [
            'tipo_item' => $datos['tipo_item'],
            'id_medicamento' => $datos['id_medicamento'],
            'id_material' => $datos['id_material'],
            'tipo_movimiento' => MovimientoStock::BAJA_VENCIMIENTO, // Usamos BAJA como egreso
            'cantidad' => $datos['cantidad'], // Cantidad positiva que será restada
            'nro_lote' => $datos['nro_lote'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'origen_tipo' => $datos['origen_tipo'],
            'origen_id' => $datos['origen_id'],
            'observaciones' => $datos['observaciones'],
            'permitir_negativo' => true
        ];

        return $this->stockService->applyMovement($datosEgreso);
    }

    /**
     * Buscar recepciones con filtros
     */
    public function buscarRecepciones(array $filtros = [])
    {
        $query = RecepcionCentral::with(['items.medicamento', 'items.material', 'usuario']);

        if (!empty($filtros['estado'])) {
            $query->porEstado($filtros['estado']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('fecha_recepcion', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('fecha_recepcion', '<=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['nro_remito'])) {
            $query->where('nro_remito', 'LIKE', "%{$filtros['nro_remito']}%");
        }

        return $query->orderBy('fecha_recepcion', 'desc')
                    ->orderBy('id_recepcion', 'desc');
    }
}