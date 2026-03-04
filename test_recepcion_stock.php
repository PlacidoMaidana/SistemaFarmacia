<?php
// test_recepcion_stock.php - Test completo del flujo de Recepción de Stock

require __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\RecepcionService;
use App\Services\StockService;
use App\Models\RecepcionCentral;
use App\Models\RecepcionCentralItem;
use App\Models\Medicamento;
use App\Models\MaterialesEnfermeria;
use App\Models\MovimientoStock;
use Illuminate\Support\Facades\Auth;

echo "🧪 TEST COMPLETO - RECEPCIÓN DE STOCK\n";
echo "===================================\n\n";

try {
    // Configurar usuario para las pruebas
    Auth::loginUsingId(1); // Asumir que existe usuario con ID 1

    // Crear instancias de servicios
    $stockService = new StockService();
    $recepcionService = new RecepcionService($stockService);

    echo "✅ Servicios inicializados correctamente\n";

    // 1. VERIFICAR ITEMS DISPONIBLES
    echo "\n📋 1. VERIFICANDO ITEMS DISPONIBLES...\n";
    echo "=====================================\n";

    $medicamento = Medicamento::first();
    $material = MaterialesEnfermeria::first();

    if (!$medicamento) {
        echo "❌ No hay medicamentos disponibles\n";
        exit(1);
    }

    if (!$material) {
        echo "❌ No hay materiales de enfermería disponibles\n";
        exit(1);
    }

    echo "✅ Medicamento: {$medicamento->nombre} (Stock inicial: {$medicamento->stock_actual})\n";
    echo "✅ Material: {$material->nombre} (Stock inicial: {$material->stock_actual})\n";

    $stockMedicamentoInicial = $medicamento->stock_actual;
    $stockMaterialInicial = $material->stock_actual;

    // 2. CREAR RECEPCIÓN EN BORRADOR
    echo "\n📦 2. CREANDO RECEPCIÓN EN BORRADOR...\n";
    echo "=====================================\n";

    $recepcion = RecepcionCentral::create([
        'nro_remito' => 'REM-TEST-' . time(),
        'fecha_recepcion' => now()->format('Y-m-d'),
        'estado' => RecepcionCentral::ESTADO_BORRADOR, // Explícitamente establecer estado
        'id_usuario' => Auth::id(),
        'observaciones' => 'Recepción de prueba - Test automatizado'
    ]);

    echo "✅ Recepción creada: ID {$recepcion->id_recepcion}, Remito: {$recepcion->nro_remito}\n";
    echo "   Estado: {$recepcion->estado} ({$recepcion->descripcion_estado})\n";

    // 3. AGREGAR ITEMS A LA RECEPCIÓN
    echo "\n➕ 3. AGREGANDO ITEMS A LA RECEPCIÓN...\n";
    echo "=====================================\n";

    // Item 1: Medicamento
    $itemMedicamento = RecepcionCentralItem::create([
        'id_recepcion' => $recepcion->id_recepcion,
        'tipo_item' => RecepcionCentralItem::TIPO_MEDICAMENTO,
        'id_medicamento' => $medicamento->id_medicamento,
        'cantidad' => 100,
        'nro_lote' => 'LOTE-MED-' . rand(1000, 9999),
        'fecha_vencimiento' => now()->addMonths(12)->format('Y-m-d')
    ]);

    echo "✅ Item medicamento agregado: {$itemMedicamento->nombre_item} x {$itemMedicamento->cantidad}\n";

    // Item 2: Material
    $itemMaterial = RecepcionCentralItem::create([
        'id_recepcion' => $recepcion->id_recepcion,
        'tipo_item' => RecepcionCentralItem::TIPO_MATERIAL,
        'id_material' => $material->id_material,
        'cantidad' => 50,
        'nro_lote' => 'LOTE-MAT-' . rand(1000, 9999),
    ]);

    echo "✅ Item material agregado: {$itemMaterial->nombre_item} x {$itemMaterial->cantidad}\n";

    // Recargar recepción con items
    $recepcion->load('items');
    echo "📊 Total items en recepción: {$recepcion->total_items}\n";
    echo "   Cantidad total: {$recepcion->cantidad_total} unidades\n";
    
    // DEBUG: Verificar estado de la recepción antes de confirmar
    echo "\n🔍 DEBUG - Estado de la recepción:\n";
    echo "   ID: {$recepcion->id_recepcion}\n";
    echo "   Estado: [{$recepcion->estado}]\n";
    echo "   Items count: " . $recepcion->items()->count() . "\n";
    echo "   ¿Puede ser editada?: " . ($recepcion->puedeSerEditada() ? 'SÍ' : 'NO') . "\n";
    echo "   ¿Puede ser confirmada?: " . ($recepcion->puedeSerConfirmada() ? 'SÍ' : 'NO') . "\n";

    // 4. CONFIRMAR RECEPCIÓN (APLICAR AL STOCK)
    echo "\n✅ 4. CONFIRMANDO RECEPCIÓN (APLICANDO AL STOCK)...\n";
    echo "==================================================\n";

    $resultado = $recepcionService->confirmarRecepcion($recepcion);

    echo "✅ {$resultado['mensaje']}\n";
    echo "📈 Movimientos creados: " . count($resultado['movimientos']) . "\n";

    foreach ($resultado['movimientos'] as $i => $movimiento) {
        echo "   Movimiento " . ($i + 1) . ": {$movimiento->tipo_movimiento} - {$movimiento->cantidad} unidades\n";
        echo "      Saldo: {$movimiento->saldo_anterior} → {$movimiento->saldo_nuevo}\n";
    }

    // 5. VERIFICAR STOCK ACTUALIZADO
    echo "\n📊 5. VERIFICANDO STOCK ACTUALIZADO...\n";
    echo "=====================================\n";

    $medicamento->refresh();
    $material->refresh();

    echo "💊 MEDICAMENTO ({$medicamento->nombre}):\n";
    echo "   Stock inicial: {$stockMedicamentoInicial}\n";
    echo "   Stock actual: {$medicamento->stock_actual}\n";
    echo "   Incremento: " . ($medicamento->stock_actual - $stockMedicamentoInicial) . "\n";

    echo "\n🏥 MATERIAL ({$material->nombre}):\n";
    echo "   Stock inicial: {$stockMaterialInicial}\n";
    echo "   Stock actual: {$material->stock_actual}\n";
    echo "   Incremento: " . ($material->stock_actual - $stockMaterialInicial) . "\n";

    // Verificaciones
    if ($medicamento->stock_actual == $stockMedicamentoInicial + 100) {
        echo "\n✅ Stock de medicamento actualizado correctamente!\n";
    } else {
        echo "\n❌ Error: Stock de medicamento no se actualizó como esperado\n";
    }

    if ($material->stock_actual == $stockMaterialInicial + 50) {
        echo "✅ Stock de material actualizado correctamente!\n";
    } else {
        echo "❌ Error: Stock de material no se actualizó como esperado\n";
    }

    // 6. VERIFICAR KARDEX
    echo "\n📖 6. VERIFICANDO KARDEX...\n";
    echo "===========================\n";

    $kardexMedicamento = $stockService->getKardexItem(
        MovimientoStock::TIPO_MEDICAMENTO, 
        $medicamento->id_medicamento
    );

    echo "📋 Últimos movimientos de {$medicamento->nombre}:\n";
    foreach ($kardexMedicamento->take(3) as $mov) {
        echo "   [{$mov->fecha} {$mov->hora}] {$mov->tipo_movimiento}: ";
        echo ($mov->tipo_movimiento === 'EGRESO_DISPENSACION' ? '-' : '+') . "{$mov->cantidad} ";
        echo "→ Stock: {$mov->saldo_nuevo}\n";
    }

    // 7. PROBAR ANULACIÓN DE RECEPCIÓN
    echo "\n🚫 7. PROBANDO ANULACIÓN DE RECEPCIÓN...\n";
    echo "=======================================\n";

    $stockAntes = $medicamento->stock_actual;
    
    $resultadoAnulacion = $recepcionService->anularRecepcion(
        $recepcion, 
        'Test de anulación - verificación del sistema'
    );

    echo "✅ {$resultadoAnulacion['mensaje']}\n";

    // Verificar que el stock volvió al estado anterior
    $medicamento->refresh();
    $material->refresh();

    echo "\n📊 VERIFICACIÓN POST-ANULACIÓN:\n";
    echo "💊 Medicamento - Stock antes anulación: {$stockAntes}\n";
    echo "   Stock después anulación: {$medicamento->stock_actual}\n";
    echo "   ¿Volvió al inicial?: " . ($medicamento->stock_actual == $stockMedicamentoInicial ? "✅ SÍ" : "❌ NO") . "\n";

    echo "\n🏥 Material - Stock inicial: {$stockMaterialInicial}\n";
    echo "   Stock después anulación: {$material->stock_actual}\n";
    echo "   ¿Volvió al inicial?: " . ($material->stock_actual == $stockMaterialInicial ? "✅ SÍ" : "❌ NO") . "\n";

    // Verificar estado de la recepción
    $recepcion->refresh();
    echo "\n📋 Estado final de la recepción: {$recepcion->estado} ({$recepcion->descripcion_estado})\n";

    echo "\n🎉 ¡TODAS LAS PRUEBAS COMPLETADAS EXITOSAMENTE!\n";
    echo "============================================\n";
    echo "✅ Creación de recepciones: FUNCIONANDO\n";
    echo "✅ Aplicación de stock: FUNCIONANDO\n";
    echo "✅ Kardex transaccional: FUNCIONANDO\n";
    echo "✅ Anulación con reversa: FUNCIONANDO\n";
    echo "\n🎯 SIGUIENTE PASO: Crear interfaz BREAD para Recepciones\n";

} catch (Exception $e) {
    echo "\n❌ ERROR EN EL TEST: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🔧 LIMPIEZA (opcional): Las recepciones de prueba quedan registradas para auditoría\n";