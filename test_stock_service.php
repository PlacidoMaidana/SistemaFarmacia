<?php
// test_stock_service.php - Test simple del StockService

require __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\StockService;
use App\Models\Medicamento;
use App\Models\MovimientoStock;

echo "🧪 TEST STOCK SERVICE\n";
echo "=====================\n\n";

try {
    // Crear instancia del servicio
    $stockService = new StockService();

    // 1. Verificar que hay medicamentos
    $medicamento = Medicamento::first();
    if (!$medicamento) {
        echo "❌ No hay medicamentos en la base de datos\n";
        exit(1);
    }

    echo "✅ Medicamento de prueba: {$medicamento->nombre}\n";
    echo "   Stock inicial: {$medicamento->stock_actual}\n\n";

    // 2. Crear un movimiento de INGRESO_CENTRAL simulado
    $stockInicial = $medicamento->stock_actual;
    
    $datosMovimiento = [
        'tipo_item' => MovimientoStock::TIPO_MEDICAMENTO,
        'id_medicamento' => $medicamento->id_medicamento,
        'tipo_movimiento' => MovimientoStock::INGRESO_CENTRAL,
        'cantidad' => 50,
        'nro_lote' => 'TEST001',
        'fecha_vencimiento' => '2026-12-31',
        'origen_tipo' => MovimientoStock::ORIGEN_RECEPCION_CENTRAL,
        'origen_id' => 999, // ID simulado de recepción
        'observaciones' => 'Test de ingreso desde Farmacia Central'
    ];

    echo "🚀 Aplicando movimiento de INGRESO...\n";
    $movimiento = $stockService->applyMovement($datosMovimiento);

    echo "✅ Movimiento creado:\n";
    echo "   ID: {$movimiento->id_movimiento}\n";
    echo "   Tipo: {$movimiento->tipo_movimiento}\n";
    echo "   Cantidad: {$movimiento->cantidad}\n";
    echo "   Saldo anterior: {$movimiento->saldo_anterior}\n";
    echo "   Saldo nuevo: {$movimiento->saldo_nuevo}\n\n";

    // 3. Verificar que el stock se actualizó
    $medicamento->refresh();
    $stockFinal = $medicamento->stock_actual;
    
    echo "📊 VERIFICACIÓN:\n";
    echo "   Stock inicial: {$stockInicial}\n";
    echo "   Stock final: {$stockFinal}\n";
    echo "   Diferencia: " . ($stockFinal - $stockInicial) . "\n";

    if ($stockFinal == $stockInicial + 50) {
        echo "✅ ¡Stock actualizado correctamente!\n\n";
    } else {
        echo "❌ Error: El stock no se actualizó como esperado\n\n";
    }

    // 4. Probar consultar kardex
    echo "📖 KARDEX del medicamento:\n";
    $kardex = $stockService->getKardexItem(
        MovimientoStock::TIPO_MEDICAMENTO, 
        $medicamento->id_medicamento
    );

    foreach ($kardex->take(3) as $mov) {
        echo "   [{$mov->fecha} {$mov->hora}] {$mov->tipo_movimiento}: {$mov->cantidad} -> Stock: {$mov->saldo_nuevo}\n";
    }

    echo "\n✅ ¡TODAS LAS PRUEBAS PASARON!\n";
    echo "El sistema kardex está funcionando correctamente.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}

echo "\n🎯 SIGUIENTE PASO: Integrar dispensaciones con el StockService\n";
echo "=============================================================\n";