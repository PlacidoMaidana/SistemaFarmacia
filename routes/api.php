<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/medicos', [ApiController::class, 'medicos'])->name('api.medicos');
Route::get('/internos', [ApiController::class, 'internos'])->name('api.internos');
Route::get('/medicamentos', [ApiController::class, 'medicamentos'])->name('api.medicamentos');