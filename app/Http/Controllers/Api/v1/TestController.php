<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function ping(): JsonResponse
    {
        return response()->json(['status' => 'ok', 'time' => now()]);
    }
    
    public function assignments(): JsonResponse
    {
        try {
            // Simple count without relationships
            $count = \App\Models\DeviceAssignment::count();
            
            return response()->json([
                'status' => 'ok',
                'total_assignments' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
