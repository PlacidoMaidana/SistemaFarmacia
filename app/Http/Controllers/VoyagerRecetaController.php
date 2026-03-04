<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class VoyagerRecetaController extends VoyagerBaseController
{
    /**
     * Override the update method to handle dispensation validation
     */
    public function update(Request $request, $id)
    {
        try {
            // Call parent update method
            return parent::update($request, $id);
        } catch (\Exception $e) {
            // Check if the error is related to dispensations validation
            if (strpos($e->getMessage(), 'dispensación') !== false) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', $e->getMessage());
            }
            
            // Re-throw other exceptions
            throw $e;
        }
    }

    /**
     * Override the store method (though new recetas don't need dispensations yet)
     */
    public function store(Request $request)
    {
        try {
            return parent::store($request);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}