<?php

// routes/web.php - Agregar estas rutas para Recepciones de Stock

use App\Http\Controllers\RecepcionCentralController;

// Rutas para Recepciones de Stock (Ingreso desde Farmacia Central)
Route::prefix('recepciones')->name('recepciones.')->group(function () {
    // CRUD básico
    Route::get('/', [RecepcionCentralController::class, 'index'])->name('index');
    Route::get('/create', [RecepcionCentralController::class, 'create'])->name('create');
    Route::post('/', [RecepcionCentralController::class, 'store'])->name('store');
    Route::get('/{recepcion}', [RecepcionCentralController::class, 'show'])->name('show');
    Route::get('/{recepcion}/edit', [RecepcionCentralController::class, 'edit'])->name('edit');
    Route::put('/{recepcion}', [RecepcionCentralController::class, 'update'])->name('update');
    Route::delete('/{recepcion}', [RecepcionCentralController::class, 'destroy'])->name('destroy');

    // Acciones especiales
    Route::post('/{recepcion}/confirmar', [RecepcionCentralController::class, 'confirmar'])->name('confirmar');
    Route::post('/{recepcion}/anular', [RecepcionCentralController::class, 'anular'])->name('anular');

    // Gestión de items
    Route::post('/{recepcion}/items', [RecepcionCentralController::class, 'agregarItem'])->name('items.store');
    Route::delete('/{recepcion}/items/{item}', [RecepcionCentralController::class, 'eliminarItem'])->name('items.destroy');

    // AJAX/API endpoints
    Route::get('/buscar/items', [RecepcionCentralController::class, 'buscarItems'])->name('buscar.items');
});

// Binding de modelos para las rutas
Route::model('recepcion', \App\Models\RecepcionCentral::class);
Route::model('item', \App\Models\RecepcionCentralItem::class);