<?php

Route::get('/test-receta', function () {
    $receta = \App\Models\Receta::first();
    
    if (!$receta) {
        return 'No hay recetas';
    }
    
    return view('test-receta', ['receta' => $receta]);
});
