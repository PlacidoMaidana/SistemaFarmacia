<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\DispensacionController;
use App\Http\Controllers\AuditoriaPsicotropicosController;
use App\Http\Controllers\RecepcionCentralController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Test route for debugging  
Route::get('/test-receta', function () {
    $receta = \App\Models\Receta::first();
    if (!$receta) {
        return 'No hay recetas';
    }
    return view('test-receta', ['receta' => $receta]);
});

// Ruta de test para recepciones (SIN autenticación)
Route::get('/test-recepciones', [App\Http\Controllers\RecepcionCentralController::class, 'index']);

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

Route::group(['middleware' => ['auth', 'admin.user']], function () {
    
    // Rutas de Dispensaciones (Módulo Centralizado)
    Route::prefix('dispensaciones')->name('dispensaciones.')->group(function () {
        Route::get('/', [DispensacionController::class, 'index'])->name('index');
        Route::get('/origen', [DispensacionController::class, 'porOrigen'])->name('por-origen');
        Route::get('/create', [DispensacionController::class, 'create'])->name('create');
        Route::post('/', [DispensacionController::class, 'store'])->name('store');
        Route::delete('/{id}', [DispensacionController::class, 'destroy'])->name('destroy');
    });

    // Rutas de Auditoría de Psicotrópicos
    Route::prefix('auditoria-psicotropicos')->name('auditoria-psicotropicos.')->group(function () {
        Route::get('/', [AuditoriaPsicotropicosController::class, 'index'])->name('index');
        Route::get('/dashboard', [AuditoriaPsicotropicosController::class, 'dashboard'])->name('dashboard');
        Route::get('/exportar', [AuditoriaPsicotropicosController::class, 'exportar'])->name('exportar');
        Route::get('/{id}', [AuditoriaPsicotropicosController::class, 'show'])->name('show');
    });
    
    // Rutas de Recepciones de Stock
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
    
    // Rutas de Recetas - Dispensaciones (Compatibilidad)
    Route::prefix('recetas')->name('recetas.')->group(function () {
        Route::get('/{id_receta}/dispensaciones', [RecetaController::class, 'dispensaciones'])->name('dispensaciones.index');
        Route::get('/{id_receta}/dispensaciones/create', [RecetaController::class, 'createDispensacion'])->name('dispensaciones.create');
        Route::post('/{id_receta}/dispensaciones', [RecetaController::class, 'storeDispensacion'])->name('dispensaciones.store');
    });
});