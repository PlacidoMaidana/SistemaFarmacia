<?php

namespace App\Services;

use App\Models\MovimientoStock;
use App\Models\Medicamento;
use App\Models\MaterialesEnfermeria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class StockService
{
    /**
     * Aplicar un movimiento de stock de manera transaccional
     * 
     * @param array $movimiento Datos del movimiento:
     *   - tipo_item: 'MEDICAMENTO' | 'MATERIAL'
     *   - id_medicamento: int (si tipo_item = MEDICAMENTO)
     *   - id_material: int (si tipo_item = MATERIAL)  
     *   - tipo_movimiento: string (INGRESO_CENTRAL, EGRESO_DISPENSACION, etc.)
     *   - cantidad: decimal
     *   - nro_lote: string (opcional)
     *   - fecha_vencimiento: date (opcional)
     *   - origen_tipo: string (DISPENSACION, RECEPCION_CENTRAL, AJUSTE)
     *   - origen_id: int
     *   - observaciones: string (opcional)
     *   - permitir_negativo: bool (opcional, default false)
     * 
     * @return MovimientoStock
     * @throws Exception
     */
    public function applyMovement(array $datos): MovimientoStock
    {
        return DB::transaction(function () use ($datos) {
            // Validar datos básicos
            $this->validarDatos($datos);
            
            // Obtener información del item (medicamento o material)
            $itemInfo = $this->obtenerInfoItem($datos);
            
            // Calcular saldos
            $saldoAnterior = $itemInfo['stock_actual'];
            $cantidad = floatval($datos['cantidad']);
            
            // Determinar si es egreso o ingreso según el tipo de movimiento
            $esEgreso = $this->esMovimientoEgreso($datos['tipo_movimiento']);
            
            $saldoNuevo = $esEgreso 
                ? $saldoAnterior - $cantidad 
                : $saldoAnterior + $cantidad;
            
            // Validar stock no negativo (si no se permite explícitamente)
            $permitirNegativo = $datos['permitir_negativo'] ?? false;
            if ($esEgreso && $saldoNuevo < 0 && !$permitirNegativo) {
                throw new Exception(
                    "Stock insuficiente para {$itemInfo['nombre']}. " .
                    "Stock actual: {$saldoAnterior}, cantidad solicitada: {$cantidad}"
                );
            }
            
            // Actualizar stock en la tabla correspondiente
            $this->actualizarStockItem($itemInfo, $saldoNuevo);
            
            // Crear registro de movimiento
            $movimiento = $this->crearRegistroMovimiento($datos, $saldoAnterior, $saldoNuevo);
            
            return $movimiento;
        });
    }

    /**
     * Aplicar múltiples movimientos de manera transaccional
     */
    public function applyMultipleMovements(array $movimientos): array
    {
        return DB::transaction(function () use ($movimientos) {
            $resultados = [];
            foreach ($movimientos as $movimiento) {
                $resultados[] = $this->applyMovement($movimiento);
            }
            return $resultados;
        });
    }

    /**
     * Validar datos del movimiento
     */
    private function validarDatos(array $datos): void
    {
        $requeridos = ['tipo_item', 'tipo_movimiento', 'cantidad', 'origen_tipo', 'origen_id'];
        
        foreach ($requeridos as $campo) {
            if (!isset($datos[$campo])) {
                throw new Exception("Campo requerido faltante: {$campo}");
            }
        }

        // Validar que tenga medicamento O material
        $tieneMedicamento = !empty($datos['id_medicamento']);
        $tieneMaterial = !empty($datos['id_material']);
        
        if (!$tieneMedicamento && !$tieneMaterial) {
            throw new Exception('Debe especificar id_medicamento o id_material');
        }
        
        if ($tieneMedicamento && $tieneMaterial) {
            throw new Exception('No puede especificar medicamento y material simultáneamente');
        }

        // Validar consistencia de tipo_item
        if ($datos['tipo_item'] === MovimientoStock::TIPO_MEDICAMENTO && !$tieneMedicamento) {
            throw new Exception('Si tipo_item es MEDICAMENTO, debe especificar id_medicamento');
        }

        if ($datos['tipo_item'] === MovimientoStock::TIPO_MATERIAL && !$tieneMaterial) {
            throw new Exception('Si tipo_item es MATERIAL, debe especificar id_material');
        }

        // Validar cantidad positiva
        if ($datos['cantidad'] <= 0) {
            throw new Exception('La cantidad debe ser mayor a 0');
        }
    }

    /**
     * Obtener información del item (medicamento o material)
     */
    private function obtenerInfoItem(array $datos): array
    {
        if ($datos['tipo_item'] === MovimientoStock::TIPO_MEDICAMENTO) {
            $medicamento = Medicamento::find($datos['id_medicamento']);
            if (!$medicamento) {
                throw new Exception("Medicamento no encontrado: {$datos['id_medicamento']}");
            }
            
            return [
                'modelo' => $medicamento,
                'tipo' => 'medicamento',
                'id' => $medicamento->id_medicamento,
                'nombre' => $medicamento->nombre,
                'stock_actual' => $medicamento->stock_actual,
                'campo_stock' => 'stock_actual'
            ];
        } else {
            $material = MaterialesEnfermeria::find($datos['id_material']);
            if (!$material) {
                throw new Exception("Material de enfermería no encontrado: {$datos['id_material']}");
            }
            
            return [
                'modelo' => $material,
                'tipo' => 'material',
                'id' => $material->id_material,
                'nombre' => $material->nombre,
                'stock_actual' => $material->stock_actual,
                'campo_stock' => 'stock_actual'
            ];
        }
    }

    /**
     * Determinar si un tipo de movimiento es egreso
     */
    private function esMovimientoEgreso(string $tipoMovimiento): bool
    {
        $tiposEgreso = [
            MovimientoStock::EGRESO_DISPENSACION,
            MovimientoStock::BAJA_VENCIMIENTO
        ];
        
        return in_array($tipoMovimiento, $tiposEgreso);
    }

    /**
     * Actualizar stock en la tabla del item
     */
    private function actualizarStockItem(array $itemInfo, int $nuevoStock): void
    {
        $itemInfo['modelo']->update([
            $itemInfo['campo_stock'] => $nuevoStock
        ]);
    }

    /**
     * Crear registro de movimiento en la tabla movimientos_stock
     */
    private function crearRegistroMovimiento(array $datos, int $saldoAnterior, int $saldoNuevo): MovimientoStock
    {
        $now = now();
        
        return MovimientoStock::create([
            'tipo_item' => $datos['tipo_item'],
            'id_medicamento' => $datos['id_medicamento'] ?? null,
            'id_material' => $datos['id_material'] ?? null,
            'tipo_movimiento' => $datos['tipo_movimiento'],
            'cantidad' => $datos['cantidad'],
            'saldo_anterior' => $saldoAnterior,
            'saldo_nuevo' => $saldoNuevo,
            'nro_lote' => $datos['nro_lote'] ?? null,
            'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null,
            'origen_tipo' => $datos['origen_tipo'],
            'origen_id' => $datos['origen_id'],
            'id_usuario' => Auth::id() ?? 1, // Fallback para casos sin autenticación
            'fecha' => $now->format('Y-m-d'),
            'hora' => $now->format('H:i:s'),
            'observaciones' => $datos['observaciones'] ?? null,
        ]);
    }

    /**
     * Obtener kardex de un item específico
     */
    public function getKardexItem(string $tipoItem, int $idItem, $fechaInicio = null, $fechaFin = null)
    {
        $query = MovimientoStock::where('tipo_item', $tipoItem);
        
        if ($tipoItem === MovimientoStock::TIPO_MEDICAMENTO) {
            $query->where('id_medicamento', $idItem);
        } else {
            $query->where('id_material', $idItem);
        }
        
        if ($fechaInicio) {
            $query->where('fecha', '>=', $fechaInicio);
        }
        
        if ($fechaFin) {
            $query->where('fecha', '<=', $fechaFin);
        }
        
        return $query->with(['medicamento', 'material', 'usuario'])
                     ->orderBy('fecha', 'asc')
                     ->orderBy('hora', 'asc')
                     ->get();
    }

    /**
     * Obtener stock actual de un item
     */
    public function getStockActual(string $tipoItem, int $idItem): int
    {
        if ($tipoItem === MovimientoStock::TIPO_MEDICAMENTO) {
            $medicamento = Medicamento::find($idItem);
            return $medicamento ? $medicamento->stock_actual : 0;
        } else {
            $material = MaterialesEnfermeria::find($idItem);
            return $material ? $material->stock_actual : 0;
        }
    }

    /**
     * Verificar si un usuario puede dispensar con stock negativo
     */
    public function puedeDispensarStockNegativo(): bool
    {
        $user = Auth::user();
        
        // Aquí implementar la lógica según roles/permisos
        // Por ahora asumimos que solo ciertos roles pueden
        return $user && ($user->hasRole('supervisor') || $user->hasRole('admin'));
    }
}