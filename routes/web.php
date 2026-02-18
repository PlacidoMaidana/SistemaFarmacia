<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\DispensacionController;

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
    
    // Rutas de Recetas - Dispensaciones (Compatibilidad)
    Route::prefix('recetas')->name('recetas.')->group(function () {
        Route::get('/{id_receta}/dispensaciones', [RecetaController::class, 'dispensaciones'])->name('dispensaciones.index');
        Route::get('/{id_receta}/dispensaciones/create', [RecetaController::class, 'createDispensacion'])->name('dispensaciones.create');
        Route::post('/{id_receta}/dispensaciones', [RecetaController::class, 'storeDispensacion'])->name('dispensaciones.store');
    });
});