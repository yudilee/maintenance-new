<?php

namespace App\Http\Controllers;

class RepairHistoryController extends Controller
{
    /**
     * Fetch repair history for a lot number from Odoo (live API call).
     */
    public function show(string $lotNumber)
    {
        try {
            $odoo = new \App\Services\OdooService();
            $result = $odoo->fetchRepairHistory($lotNumber);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch repair history: ' . $e->getMessage(),
                'data' => [],
            ]);
        }
    }
}
